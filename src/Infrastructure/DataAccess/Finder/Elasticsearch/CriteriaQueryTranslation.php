<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\In;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaContainerInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\CustomCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Annulus;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Box;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Circle;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Polygon;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Query\RangeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SpatialCriteria;

class CriteriaQueryTranslation implements QueryTranslationInterface
{
    const QUERY_FOR_EMPTY = '__empty';

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function translate(QueryInterface $query)
    {
        Assertion::isInstanceOf($query, CriteriaQueryInterface::CLASS);

        $es_query = [
            'from' => $query->getOffset(),
            'size' => $query->getLimit(),
            'body' => $this->buildBody($query)
        ];

        return $es_query;
    }

    protected function buildBody(QueryInterface $query)
    {
        $filter_criteria_list = $query->getFilterCriteriaList();
        $default_filters = new CriteriaList;
        foreach ($this->config->get('query_filters', []) as $attribute_path => $attribute_value) {
            $default_filters->push(new AttributeCriteria($attribute_path, new Equals($attribute_value)));
        }
        if (!$default_filters->isEmpty()) {
            $default_filters->push($filter_criteria_list);
            $filter_criteria_list = $default_filters;
        }

        $es_filter = $this->translateFilters($filter_criteria_list);
        $es_query = $this->translateQueries($query->getSearchCriteriaList());
        if (!empty($es_filter)) {
            $es_query = [
                'bool' => [
                    'must' => $es_query,
                    'filter' => $es_filter
                ]
            ];
        }

        return [
            'query' => $es_query,
            'sort' => $this->buildSort($query)
        ];
    }

    protected function translateQueries(CriteriaList $criteria_list)
    {
        if ($criteria_list->isEmpty()) {
            return [ 'match_all' => [] ];
        } else {
            // @todo atm we only support global search on the _all field
            // more complex search query building will follow up
            $search_criteria = $criteria_list->getFirst();
            if (!$search_criteria instanceof SearchCriteria) {
                throw new RuntimeError(
                    sprintf('Only instances of %s supported as search-criteria.', SearchCriteria::CLASS)
                );
            }

            $phrase = $search_criteria->getPhrase();

            if (preg_match('~^suggest:([\.\w]+)=(.+)~', $phrase, $matches)) {
                $suggest_field_parts = [];
                // strip the 'type' portion of the attribute-path, to address props the way ES expects
                foreach (explode('.', $matches[1]) as $i => $field_part) {
                    if ($i % 2 === 0) {
                        $suggest_field_parts[] = $field_part;
                    }
                }
                $suggest_field = implode('.', $suggest_field_parts);
                $suggest_field .= '.suggest'; // convention: multi field for suggestions
                $suggest_term = $matches[2];

                return [
                    'match_phrase_prefix' => [
                        $suggest_field => [ 'query' => $suggest_term, 'max_expansions' => 15 ]
                    ]
                ];
            } else {
                return $this->buildSearchQuery($search_criteria);
            }
        }
    }

    protected function translateFilters(CriteriaList $filter_criteria_list)
    {
        $elasticsearch_filters = [];
        foreach ($filter_criteria_list as $criteria) {
            if ($criteria instanceof CriteriaContainerInterface) {
                $operator = $criteria->getOperator();
                $filters = $this->translateFilters($criteria->getCriteriaList());
                if (!empty($filters)) {
                    if (isset($elasticsearch_filters[$operator])) {
                        $elasticsearch_filters[] = array_merge_recursive(
                            $elasticsearch_filters[$operator],
                            $filters
                        );
                    } else {
                        $elasticsearch_filters[] = $filters;
                    }
                }
            } elseif ($criteria instanceof AttributeCriteria) {
                $elasticsearch_filters[] = $this->buildFilterFor($criteria);
            } elseif ($criteria instanceof RangeCriteria) {
                $elasticsearch_filters[] = $this->buildRangeFilterFor($criteria);
            } elseif ($criteria instanceof SpatialCriteria) {
                $elasticsearch_filters[] = $this->buildSpatialFilterFor($criteria);
            } elseif ($criteria instanceof CustomCriteria) {
                $elasticsearch_filters[] = $criteria->getQueryPart();
            } else {
                throw new RuntimeError(
                    sprintf('Invalid criteria type %s given to %s', get_class($criteria), static::CLASS)
                );
            }
        }

        if (count($elasticsearch_filters)) {
            return [ $filter_criteria_list->getOperator() => $elasticsearch_filters ];
        } else {
            return [];
        }
    }

    protected function buildFilterFor(CriteriaInterface $criteria)
    {
        $negate_filter = false;
        $attribute_value = $criteria->getComparison()->getComparand();
        $attribute_path = $criteria->getAttributePath();

        if (is_array($attribute_value)) {
            $filter = $this->buildTermsFilter($criteria);
            if ($criteria->getComparison()->isInverted()) {
                return [ 'not' => $filter ];
            }
            return $filter;
        }

        if ($criteria->getComparison()->isInverted() || strpos($attribute_value, '!') === 0) {
            $negate_filter = true;
            $attribute_value = substr($attribute_value, 0);
        }
        if ($attribute_value === self::QUERY_FOR_EMPTY) {
            $attr_filter = $this->buildMissingFilter($criteria);
        } else {
            $attr_filter = $this->buildTermFilter($criteria);
        }

        return $negate_filter ? $this->negateFilter($attr_filter) : $attr_filter;
    }

    protected function buildRangeFilterFor(CriteriaInterface $criteria)
    {
        $attribute_path = $criteria->getAttributePath();

        $comparisons = null;
        foreach ($criteria->getItems() as $comparison) {
            $comparand = $comparison->getComparand();
            // format date range queries
            if (!is_numeric($comparand) && $ts = strtotime($comparand)) {
                // @todo support for date ranges beyond unix timestamp range
                $comparand = date('c', $ts);
                $comparisons['format'] = "yyyy-MM-dd'T'HH:mm:ssZ";
            }
            $comparisons[$comparison->getComparator()] = $comparand;
        }

        return [
            'range' => [ $attribute_path => $comparisons ]
        ];
    }

    protected function buildSpatialFilterFor(CriteriaInterface $criteria)
    {
        $attribute_path = $criteria->getAttributePath();
        $comparison = $criteria->getComparison();
        $geometry = $comparison->getComparand();

        if ($comparison instanceof In) {
            if ($geometry instanceof Circle) {
                $filter = [
                    'geo_distance' => [
                        'distance' => $geometry->getRadius(),
                        $attribute_path => (string)$geometry->getCenter()
                    ]
                ];
            } elseif ($geometry instanceof Annulus) {
                $filter = [
                    'geo_distance_range' => [
                        'from' => $geometry->getInnerRadius(),
                        'to' => $geometry->getOuterRadius(),
                        $attribute_path => (string)$geometry->getCenter()
                    ]
                ];
            } elseif ($geometry instanceof Box) {
                $filter = [
                    'geo_bounding_box' => [
                        $attribute_path => [
                            'top_left' => (string)$geometry->getTopLeft(),
                            'bottom_right' => (string)$geometry->getBottomRight()
                        ]
                    ]
                ];
            } elseif ($geometry instanceof Polygon) {
                $filter = [
                    'geo_polygon' => [
                        $attribute_path => [
                            'points' => array_map('strval', $geometry->toArray())
                        ]
                    ]
                ];
            } else {
                throw new RuntimeError(
                    sprintf('Invalid comparand %s given to %s', get_class($criteria), static::CLASS)
                );
            }
        } else {
            throw new RuntimeError(
                sprintf('Invalid spatial query comparator %s given to %s', get_class($criteria), static::CLASS)
            );
        }

        return $filter;
    }

    protected function buildMissingFilter(CriteriaInterface $criteria)
    {
        $attribute_path = $criteria->getAttributePath();

        $multi_field_mapped_attributes = (array)$this->config->get('multi_fields', []);
        if (in_array($attribute_path, $multi_field_mapped_attributes)) {
            $attribute_path = $attribute_path . '.filter';
        }

        return [
            'missing' => [
                'field' => $attribute_path,
                'existence' => true,
                'null_value' => true
            ]
        ];
    }

    protected function buildTermFilter(CriteriaInterface $criteria)
    {
        $attribute_value = $criteria->getComparison()->getComparand();
        if (strpos($attribute_value, '!') === 0) {
            $attribute_value = substr($attribute_value, 1);
        }

        $attribute_path = $criteria->getAttributePath();

        $multi_field_mapped_attributes = (array)$this->config->get('multi_fields', []);
        if (in_array($attribute_path, $multi_field_mapped_attributes)) {
            $attribute_path = $attribute_path . '.filter';
        }

        $attr_filter = [ 'term' => [ $attribute_path => $attribute_value ] ];
        /*
        $terms = explode(',', $attribute_value);
        if (count($terms) > 1) {
            $attr_filter = [ 'terms' => [ $attribute_path => $terms] ];
        } else {
            $attr_filter = [ 'term' => [ $attribute_path => $terms[0]] ];
        }
        */
        return $attr_filter;
    }

    protected function buildTermsFilter(CriteriaInterface $criteria)
    {
        $attribute_value = $criteria->getComparison()->getComparand();
        $attribute_path = $criteria->getAttributePath();

        $multi_field_mapped_attributes = (array)$this->config->get('multi_fields', []);
        if (in_array($attribute_path, $multi_field_mapped_attributes)) {
            $attribute_path = $attribute_path . '.filter';
        }

        $attr_filter = [ 'terms' => [ $attribute_path => $attribute_value ] ];

        return $attr_filter;
    }

    protected function negateFilter(array $filter)
    {
        return [ 'not' => $filter ];
    }

    protected function buildSort(QueryInterface $query)
    {
        $sorts = [];
        $dynamic_mappings = $this->getDynamicMappings();

        foreach ($query->getSortCriteriaList() as $sort_criteria) {
            $attribute_path = $sort_criteria->getAttributePath();
            $sort = [ 'order' => $sort_criteria->getDirection() ];
            if (isset($dynamic_mappings[$attribute_path])) {
                $sort['unmapped_type'] = $dynamic_mappings[$attribute_path];
            }
            $multi_field_mapped_attributes = (array)$this->config->get('multi_fields', []);
            if (in_array($attribute_path, $multi_field_mapped_attributes)) {
                $attribute_path = $attribute_path . '.sort';
            }
            $sorts[][$attribute_path] = $sort;
        }

        return $sorts;
    }

    protected function buildSearchQuery(SearchCriteria $search_criteria)
    {
        $phrase = $search_criteria->getPhrase();
        $field = trim($search_criteria->getAttributePath());
        if (empty($field)) {
            $field = '_all';
        }

        $search_query_settings = array_merge(
            [
                'query' => $phrase,
                'type' => 'boolean',
                'operator' => 'and',
                // @codingStandardsIgnoreStart
                // @see https://www.elastic.co/guide/en/elasticsearch/reference/2.4/query-dsl-match-query.html#query-dsl-match-query-fuzziness
                // @codingStandardsIgnoreEnd
            ],
            (array)$this->config->get('search_query_settings', [])
        );

        $search_query = [
            'match' => [
                $field => $search_query_settings
            ]
        ];

        return $search_query;
    }

    protected function getDynamicMappings()
    {
        return array_merge(
            (array)$this->config->get('dynamic_mappings', []),
            [
                'identifier' => 'string',
                'referenced_identifier' => 'string',
                'uuid' => 'string',
                'language' => 'string',
                'version' => 'long',
                'revision' => 'long',
                'short_id' => 'long',
                'created_at' => 'date',
                'modified_at' => 'date',
                'workflow_state' => 'string'
            ]
        );
    }
}

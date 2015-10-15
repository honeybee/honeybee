<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\QueryBuilderInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaContainerInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;

class ElasticsearchQueryTranslation implements QueryTranslationInterface
{
    const QUERY_FOR_EMPTY = '__empty';

    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function translate(QueryInterface $query)
    {
        $es_query = [
            'from' => $query->getOffset(),
            'size' => $query->getLimit(),
            'body' => $this->buildBody($query)
        ];

        if ($this->config->has('index')) {
            $es_query['index'] = $this->config->get('index');
        }
        if ($this->config->has('type')) {
            $es_query['type'] = $this->config->get('type');
        }

        return $es_query;
    }

    protected function buildBody(QueryInterface $query)
    {
        $filter_criteria_list = $query->getFilterCriteriaList();
        foreach ($this->config->get('query_filters', []) as $attribute_path => $attribute_value) {
            $criteria = new AttributeCriteria($attribute_path, $attribute_value);
            $filter_criteria_list->push($criteria);
        }

        $es_filter = $this->translateFilters($filter_criteria_list);
        $es_query = $this->translateQueries($query->getSearchCriteriaList());

        if (!empty($es_filter)) {
            $es_query = [ 'filtered' => [ 'query' => $es_query, 'filter' => $es_filter ] ];
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
                $suggest_field = $matches[1];
                $suggest_term = $matches[2];

                return [
                    'match_phrase_prefix' => [
                        $suggest_field => [ 'query' => $suggest_term, 'max_expansions' => 15 ]
                    ]
                ];
            } else {
                return [
                    'match' => [
                        '_all' => [ 'query' => $search_criteria->getPhrase(), 'type' => 'phrase_prefix' ]
                    ]
                ];
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
            } else if ($criteria instanceof AttributeCriteria) {
                $elasticsearch_filters[] = $this->buildFilterFor($criteria);
            } else {
                throw new RuntimeError(
                    sprintf('Invalid criteria type %s given to %s', get_class($criteria), staic::CLASS)
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
        $attribute_value = $criteria->getValue();
        $attribute_path = $criteria->getAttributePath();

        if (is_array($attribute_value)) {
            return [ 'terms' => [ $attribute_path => $attribute_value ] ];
        }

        if (strpos($attribute_value, '!') === 0) {
            $negate_filter = true;
        }

        if ($attribute_value === self::QUERY_FOR_EMPTY) {
            $attr_filter = $this->buildMissingFilter($criteria);
        } else {
            $attr_filter = $this->buildTermFilter($criteria);
        }

        return $negate_filter ? $this->negateFilter($attr_filter) : $attr_filter;
    }

    protected function buildMissingFilter(CriteriaInterface $criteria)
    {
        return [
            'missing' => [
                'field' => $criteria->getAttributePath(),
                'existence' => true,
                'null_value' => true
            ]
        ];
    }

    protected function buildTermFilter(CriteriaInterface $criteria)
    {
        $attribute_value = $criteria->getValue();
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
            $sorts[][$attribute_path] = $sort;
        }

        return $sorts;
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

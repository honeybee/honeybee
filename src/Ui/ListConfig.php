<?php

namespace Honeybee\Ui;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\GreaterThanOrEquals;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\In;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThan;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\LessThanOrEquals;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Annulus;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Box;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Circle;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Point;
use Honeybee\Infrastructure\DataAccess\Query\Geometry\Polygon;
use Honeybee\Infrastructure\DataAccess\Query\RangeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SpatialCriteria;
use Trellis\Common\Object;

class ListConfig extends Object implements ListConfigInterface
{
    protected $filter;
    protected $limit;
    protected $offset;
    protected $search;
    protected $settings;
    protected $sort;

    public function __construct(array $state = [])
    {
        $this->filter = [];
        $this->limit = 50;
        $this->offset = 0;
        $this->search = '';
        $this->settings = new Settings([]);
        $this->sort = '';

        parent::__construct($state);
    }

    public function asQuery()
    {
        $filter_criteria = [];
        if ($this->hasFilter()) {
            foreach ($this->getFilter() as $attribute_path => $value) {
                if (!preg_match_all('#(?<criteria>\w+)\((?<value>.+)\)(?:,|$)#U', $value, $matches, PREG_SET_ORDER)) {
                    $matches = explode(',', $value);
                    foreach ($matches as $match) {
                        $filter_criteria[] = $this->buildAttributeFilterFor($attribute_path, $match);
                    }
                } else {
                    foreach ($matches as $match) {
                        switch ($match['criteria']) {
                            case 'range':
                                $filter_criteria[] = $this->buildRangeFilterFor($attribute_path, $match['value']);
                                break;
                            case 'spatial':
                                $filter_criteria[] = $this->buildSpatialFilterFor($attribute_path, $match['value']);
                                break;
                            case 'match':
                                $filter_criteria[] = $this->buildMatchFilterFor($attribute_path, $match['value']);
                                break;
                            default:
                                throw new RuntimeError(sprintf('Unsupported query criteria "%s"', $match['criteria']));
                        }
                    }
                }
            }
        }

        $filter_criteria_list = new CriteriaList($filter_criteria);

        $search_criteria_list = new CriteriaList;
        if ($this->hasSearch()) {
            $search_criteria_list->push(new SearchCriteria($this->getSearch()));
        }

        $sort_criteria_list = new CriteriaList;
        if ($this->hasSort()) {
            $sort_string = $this->getSort();
            $sort = [];
            $sort_fields = explode(',', $sort_string);
            foreach ($sort_fields as $sort_field) {
                if (!preg_match('/^([\w\.]+):(asc|desc)$/u', $sort_field)) {
                    throw new RuntimeError('The sort value has a wrong format. Should be: field1:asc,field2:desc');
                }
                list($attribute_path, $direction) = explode(':', $sort_field);
                $sort_criteria_list->push(new SortCriteria($attribute_path, $direction));
            }
        } else {
            $sort_criteria_list->push(new SortCriteria('modified_at'));
        }

        return new CriteriaQuery(
            $search_criteria_list,
            $filter_criteria_list,
            $sort_criteria_list,
            $this->getOffset(),
            $this->getLimit()
        );
    }

    protected function buildAttributeFilterFor($attribute_path, $value)
    {
        $comparison = 0 === strpos($value, '!')
            ? new Equals(ltrim($value, '!'), true)
            : new Equals($value);

        return new AttributeCriteria($attribute_path, $comparison);
    }

    protected function buildRangeFilterFor($attribute_path, $value)
    {
        if (!preg_match_all('#(?<comparator>[!\w]+):(?<comparand>.+?)(?:,|$)#', $value, $matches, PREG_SET_ORDER)) {
            throw new RuntimeError(sprintf('Invalid range criteria value "%s"', $value));
        }

        $arguments = [];
        foreach ($matches as $match) {
            $arguments[] = $this->buildComparisonFor($match['comparator'], $match['comparand']);
        }

        return new RangeCriteria($attribute_path, ...$arguments);
    }

    protected function buildSpatialFilterFor($attribute_path, $value)
    {
        if (!preg_match_all('#(?<comparator>[!\w]+):(?<comparand>.+?)$#', $value, $matches, PREG_SET_ORDER)) {
            throw new RuntimeError(sprintf('Invalid spatial criteria value "%s"', $value));
        }

        $arguments = [];
        foreach ($matches as $match) {
            $arguments[] = $this->buildComparisonFor($match['comparator'], $match['comparand']);
        }

        return new SpatialCriteria($attribute_path, ...$arguments);
    }

    protected function buildComparisonFor($comparator, $comparand)
    {
        switch ($comparator) {
            case 'eq':
                $comparison = new Equals($comparand);
                break;
            case '!eq':
                $comparison = new Equals($comparand, true);
                break;
            case 'gt':
                $comparison = new GreaterThan($comparand);
                break;
            case 'gte':
                $comparison = new GreaterThanOrEquals($comparand);
                break;
            case 'lt':
                $comparison = new LessThan($comparand);
                break;
            case 'lte':
                $comparison = new LessThanOrEquals($comparand);
                break;
            case 'in':
                // @todo support non-geometric subjects
                $geometry = $this->buildGeometryFor($comparand);
                $comparison = new In($geometry);
                break;
            case '!in':
                $geometry = $this->buildGeometryFor($comparand);
                $comparison = new In($geometry, true);
                break;
            default:
                throw new RuntimeError(sprintf('Unsupported criteria comparator "%s"', $comparator));
        }

        return $comparison;
    }

    //@todo support geohash
    protected function buildGeometryFor($value)
    {
        preg_match('#(?<geometry>\w+)\((?<args>.+)\)$#', $value, $match);
        switch ($match['geometry']) {
            case 'annulus':
                if (!preg_match('#^(?<center>\[.+\]),(?<inner>.+),(?<outer>.+)$#', $match['args'], $arguments)) {
                    throw new RuntimeError(sprintf('Invalid annulus arguments "%s"', $match['args']));
                }
                $center = $this->createPoints($arguments['center'])[0];
                $geometry = new Annulus($center, $arguments['inner'], $arguments['outer']);
                break;
            case 'circle':
                if (!preg_match('#^(?<center>\[.+\]),(?<radius>.+)$#', $match['args'], $arguments)) {
                    throw new RuntimeError(sprintf('Invalid circle arguments "%s"', $match['args']));
                }
                $center = $this->createPoints($arguments['center'])[0];
                $geometry = new Circle($center, $arguments['radius']);
                break;
            case 'box':
                $geometry = new Box(...$this->createPoints($value));
                break;
            case 'polygon':
                $geometry = new Polygon($this->createPoints($value));
                break;
            default:
                throw new RuntimeError(sprintf('Unsupported geometry "%s"', $match['geometry']));
        }

        return $geometry;
    }

    protected function createPoints($value)
    {
        if (!preg_match_all('#(?:(?<point>\[[\d\.,]+\]),?)#', $value, $matches)) {
            throw new RuntimeError(sprintf('Invalid point arguments "%s"', $value));
        }

        $points = [];
        foreach ($matches['point'] as $point_values) {
            $points[] = new Point(...explode(',', trim($point_values, '[]')));
        }

        return $points;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function hasFilter()
    {
        return !empty($this->filter);
    }

    public function getLimit()
    {
        return (int)$this->limit;
    }

    public function hasLimit()
    {
        return (((int)$this->limit) > 0);
    }

    public function getOffset()
    {
        return (int)$this->offset;
    }

    public function hasOffset()
    {
        return (((int)$this->offset) > 0);
    }

    public function getSearch()
    {
        return $this->search;
    }

    public function hasSearch()
    {
        return !empty($this->search);
    }

    public function getSort()
    {
        return $this->sort;
    }

    public function hasSort()
    {
        return !empty($this->sort);
    }

    public function getSettings()
    {
        return $this->settings;
    }

    protected function setSettings($settings)
    {
        if (is_array($settings)) {
            $settings = new Settings($settings);
        }

        $this->settings = $settings;
    }
}

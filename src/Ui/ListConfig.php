<?php

namespace Honeybee\Ui;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
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
        $filter_criteria_list = new CriteriaList;
        if ($this->hasFilter()) {
            foreach ($this->getFilter() as $attribute_path => $value) {
                $filter_criteria_list->push(new AttributeCriteria($attribute_path, new Equals($value)));
            }
        }

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

        return new Query(
            $search_criteria_list,
            $filter_criteria_list,
            $sort_criteria_list,
            $this->getOffset(),
            $this->getLimit()
        );
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

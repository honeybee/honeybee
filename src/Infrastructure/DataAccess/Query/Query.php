<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class Query implements QueryInterface
{
    protected $search_criteria_list;

    protected $filter_criteria_list;

    protected $sort_criteria_list;

    protected $offset;

    protected $limit;

    public function __construct(
        CriteriaList $search_criteria_list,
        CriteriaList $filter_criteria_list,
        CriteriaList $sort_criteria_list,
        $offset,
        $limit
    ) {
        $this->search_criteria_list = $search_criteria_list;
        $this->filter_criteria_list = $filter_criteria_list;
        $this->sort_criteria_list = $sort_criteria_list;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function getSearchCriteriaList()
    {
        return $this->search_criteria_list;
    }

    public function getFilterCriteriaList()
    {
        return $this->filter_criteria_list;
    }

    public function getSortCriteriaList()
    {
        return $this->sort_criteria_list;
    }

    public function getSorting()
    {
        return $this->sorting;
    }

    public function createCopyWith(array $new_state)
    {
        return new static(
            isset($new_state['search_criteria_list'])
                ? $new_state['search_criteria_list']
                : $this->getSearchCriteriaList(),
            isset($new_state['filter_criteria_list'])
                ? $new_state['filter_criteria_list']
                : $this->getFilterCriteriaList(),
            isset($new_state['sort_criteria_list'])
                ? $new_state['sort_criteria_list']
                : $this->getSortCriteriaList(),
            isset($new_state['offset'])
                ? $new_state['offset']
                : $this->getOffset(),
            isset($new_state['limit'])
                ? $new_state['limit']
                : $this->getLimit()
        );
    }

    public function __toString()
    {
        return sprintf(
            'QUERY: SEARCH %s FILTER %s SORT %s %s %s',
            $this->search_criteria_list,
            $this->filter_criteria_list,
            $this->sort_criteria_list,
            isset($this->limit) ? ('LIMIT ' . $this->limit) : '',
            isset($this->offset) ? ('OFFSET ' . $this->offset) : ''
        );
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}

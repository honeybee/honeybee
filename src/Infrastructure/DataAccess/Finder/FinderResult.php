<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

class FinderResult implements FinderResultInterface
{
    protected $results;

    protected $total_count;

    protected $offset;

    public function __construct(array $results = [], $total_count = 0, $offset = 0)
    {
        $this->results = $results;
        $this->total_count = $total_count;
        $this->offset = 0;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function hasResults()
    {
        return !empty($this->results);
    }

    public function getFirstResult()
    {
        return $this->hasResults() ? $this->results[0] : null;
    }

    public function getTotalCount()
    {
        return $this->total_count;
    }
}

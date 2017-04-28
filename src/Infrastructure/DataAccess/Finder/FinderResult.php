<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Assert\Assertion;

class FinderResult implements FinderResultInterface
{
    protected $results;

    protected $total_count;

    protected $offset;

    protected $cursor;

    public function __construct(array $results = [], $total_count = 0, $offset = 0, $cursor = null)
    {
        Assertion::integer($total_count);
        Assertion::integer($offset);

        $this->results = $results;
        $this->total_count = $total_count;
        $this->offset = $offset;
        $this->cursor = $cursor;
    }

    public static function makeEmpty()
    {
        return new static;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function getCount()
    {
        return count($this->results);
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getCursor()
    {
        return $this->cursor;
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

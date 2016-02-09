<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class Comparison
{
    const EQUALS = 'eq';

    const GREATER_THAN = 'gt';

    const LESS_THAN = 'lt';

    const GREATER_THAN_EQUAL = 'gte';

    const LESS_THAN_EQUAL = 'lte';

    protected $comparator;

    protected $comparand;

    public function __construct($comparator, $comparand)
    {
        $this->comparator = $comparator;
        $this->comparand = $comparand;
    }

    public function getComparator()
    {
        return $this->comparator;
    }

    public function getComparand()
    {
        return $this->comparand;
    }

    public function __toString()
    {
        return $this->comparator . ' ' . $this->comparand;
    }
}
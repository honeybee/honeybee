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

    protected $inverted;

    public function __construct($comparator, $comparand, $inverted = false)
    {
        $this->comparator = $comparator;
        $this->comparand = $comparand;
        $this->inverted = $inverted;
    }

    public function getComparator()
    {
        return $this->comparator;
    }

    public function getComparand()
    {
        return $this->comparand;
    }

    public function isInverted()
    {
        return $this->inverted;
    }

    public function __toString()
    {
        $string = strtoupper($this->comparator) . ' ' . $this->comparand;
        if ($this->inverted) {
            $string = 'not ' . $string;
        }

        return $string;
    }

    public function toArray()
    {
        return [
            '@type' => static::CLASS,
            'comparator' => $this->comparator,
            'comparand' => $this->comparand,
            'inverted' => $this->inverted
        ];
    }
}

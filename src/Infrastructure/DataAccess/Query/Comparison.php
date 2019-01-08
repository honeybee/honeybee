<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Trellis\Common\BaseObject;

class Comparison extends BaseObject implements ComparisonInterface
{
    const EQUALS = 'eq';

    const GREATER_THAN = 'gt';

    const LESS_THAN = 'lt';

    const GREATER_THAN_EQUAL = 'gte';

    const LESS_THAN_EQUAL = 'lte';

    const IN = 'in';

    protected $comparator;

    protected $comparand;

    protected $inverted;

    public function __construct($comparator, $comparand, $inverted = false)
    {
        parent::__construct([]);
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
            $string = 'NOT ' . $string;
        }

        return $string;
    }
}

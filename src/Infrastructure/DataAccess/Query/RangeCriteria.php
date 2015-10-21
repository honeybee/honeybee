<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

class RangeCriteria implements CriteriaInterface
{
    protected $attribute_path;

    protected $comparator;

    protected $lower;

    protected $upper;

    public function __construct($attribute_path, $lower, $upper)
    {
        $this->attribute_path = $attribute_path;
        $this->lower = $lower;
        $this->upper = $upper;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getLower()
    {
        return $this->lower;
    }

    public function getUpper()
    {
        return $this->upper;
    }

    public function __toString()
    {
        return sprintf(
            'ATTRIBUTE %s lower: %s, upper: %s',
            $this->attribute_path,
            strtoupper($this->comparator),
            $this->lower,
            $this->upper
        );
    }
}

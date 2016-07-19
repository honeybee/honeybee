<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Trellis\Common\Object;

class SpatialCriteria extends Object implements CriteriaInterface
{
    protected $attribute_path;

    protected $comparison;

    public function __construct($attribute_path, Comparison $comparison)
    {
        $this->attribute_path = $attribute_path;
        $this->comparison = $comparison;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getComparison()
    {
        return $this->comparison;
    }

    public function __toString()
    {
        return sprintf(
            'ATTRIBUTE %s SPATIAL %s',
            $this->attribute_path,
            $this->comparison
        );
    }
}

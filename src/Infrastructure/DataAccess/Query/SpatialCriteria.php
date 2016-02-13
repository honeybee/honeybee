<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\Geometry\PositionInterface;

class SpatialCriteria implements CriteriaInterface
{
    protected $attribute_path;

    protected $position;

    public function __construct($attribute_path, PositionInterface $position)
    {
        $this->attribute_path = $attribute_path;
        $this->position = $position;
    }

    public function getAttributePath()
    {
        return $this->attribute_path;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function __toString()
    {
        return sprintf(
            'ATTRIBUTE %s SPATIAL %s',
            $this->attribute_path,
            $this->position
        );
    }
}

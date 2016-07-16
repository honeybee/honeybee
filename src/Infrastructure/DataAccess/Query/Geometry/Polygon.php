<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

use Trellis\Collection\TypedList;

class Polygon extends TypedList implements GeometryInterface
{
    public function __construct(array $points = [])
    {
        parent::__construct(Point::CLASS, $points);
    }

    public function __toString()
    {
        return 'POLYGON ' . implode(';', $this->items);
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['@type'] = static::CLASS;

        return $array;
    }
}

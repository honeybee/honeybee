<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

use Trellis\Common\Collection\TypedList;

class Polygon extends TypedList implements GeometryInterface
{
    protected function getItemImplementor()
    {
        return Point::CLASS;
    }

    public function __toString()
    {
        return 'POLYGON ' . implode(';', $this->items);
    }
}

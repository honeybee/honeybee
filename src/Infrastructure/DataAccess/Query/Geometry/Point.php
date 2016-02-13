<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

class Point implements GeometryInterface
{
    protected $x;

    protected $y;

    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX()
    {
        return $this->x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function toArray()
    {
        return [ $this->x, $this->y ];
    }

    public function __toString()
    {
        return sprintf('%s,%s', $this->x, $this->y);
    }
}

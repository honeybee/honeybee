<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

class Circle implements GeometryInterface
{
    protected $center;

    protected $radius;

    public function __construct(Point $center, $radius)
    {
        $this->center = $center;
        $this->radius = $radius;
    }

    public function getCenter()
    {
        return $this->center;
    }

    public function getRadius()
    {
        return $this->radius;
    }

    public function __toString()
    {
        return sprintf(
            'CIRCLE CENTER %s RADIUS %s',
            $this->center,
            $this->radius
        );
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

class Annulus implements GeometryInterface
{
    protected $center;

    protected $inner_radius;

    protected $outer_radius;

    public function __construct(Point $center, $inner_radius, $outer_radius)
    {
        $this->center = $center;
        $this->inner_radius = $inner_radius;
        $this->outer_radius = $outer_radius;
    }

    public function getCenter()
    {
        return $this->center;
    }

    public function getInnerRadius()
    {
        return $this->inner_radius;
    }

    public function getOuterRadius()
    {
        return $this->outer_radius;
    }

    public function __toString()
    {
        return sprintf(
            'ANNULUS CENTER %s FROM %s TO %s',
            $this->center,
            $this->inner_radius,
            $this->outer_radius
        );
    }

    public function toArray()
    {
        return [
            '@type' => static::CLASS,
            'center' => $this->center,
            'inner_radius' => $this->inner_radius,
            'outer_radius' => $this->outer_radius
        ];
    }
}

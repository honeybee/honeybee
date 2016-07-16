<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

class Inside implements PositionInterface
{
    protected $geometry;

    public function __construct(GeometryInterface $geometry)
    {
        $this->geometry = $geometry;
    }

    public function getGeometry()
    {
        return $this->geometry;
    }

    public function __toString()
    {
        return 'INSIDE ' . $this->geometry;
    }

    public function toArray()
    {
        return [
            '@type' => static::CLASS,
            'geometry' => $this->geometry->toArray()
        ];
    }
}

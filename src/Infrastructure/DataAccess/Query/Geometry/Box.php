<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

class Box extends Polygon
{
    public function __construct(Point $top_left, Point $bottom_right)
    {
        parent::__construct([ $top_left, $bottom_right ]);
    }

    public function getTopLeft()
    {
        return $this->getItem(0);
    }

    public function getBottomRight()
    {
        return $this->getItem(1);
    }

    public function __toString()
    {
        return sprintf(
            'BOX %s;%s',
            $this->getTopLeft(),
            $this->getBottomRight()
        );
    }
}

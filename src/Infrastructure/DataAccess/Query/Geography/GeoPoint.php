<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geography;

use Honeybee\Infrastructure\DataAccess\Query\Geometry\Point;

class GeoPoint extends Point
{
    public function __construct($longitude, $latitude)
    {
        parent::__construct($longitude, $latitude);
    }

    public function getLongitude()
    {
        return $this->getX();
    }

    public function getLatitude()
    {
        return $this->getY();
    }

    public function __toString()
    {
        return sprintf('%s,%s', $this->y, $this->x);
    }
}

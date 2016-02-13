<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geography;

use Honeybee\Infrastructure\DataAccess\Query\Geometry\Point;

class GeoHash extends Point
{
    protected $geoHash;

    public function __construct($geoHash)
    {
        $this->geoHash = $geoHash;
    }

    public function getGeoHash()
    {
        return $this->geoHash;
    }

    public function __toString()
    {
        return $this->geoHash;
    }
}

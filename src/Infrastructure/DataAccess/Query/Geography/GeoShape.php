<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geography;

use Honeybee\Infrastructure\DataAccess\Query\Geometry\Polygon;

class GeoShape
{
    protected $exterior;

    protected $holes;

    public function __construct(Polygon $exterior, array $holes = [])
    {
        $this->exterior = $exterior;
        $this->holes = $holes;
    }
}

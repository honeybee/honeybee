<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Geometry;

interface PositionInterface
{
    public function getGeometry();

    public function toArray();
}

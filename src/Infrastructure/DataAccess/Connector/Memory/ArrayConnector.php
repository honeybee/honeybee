<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Memory;

use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Trellis\Collection\Map;

class ArrayConnector extends Connector
{
    protected function connect()
    {
        return new Map;
    }
}

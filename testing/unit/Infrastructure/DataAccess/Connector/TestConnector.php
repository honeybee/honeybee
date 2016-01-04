<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\DataAccess\Connector\Connector;

class TestConnector extends Connector
{
    public function connect()
    {
        return new \stdClass;
    }
}

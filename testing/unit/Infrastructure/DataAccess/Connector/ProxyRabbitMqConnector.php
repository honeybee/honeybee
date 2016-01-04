<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;

class ProxyRabbitMqConnector extends RabbitMqConnector
{
    protected function connect()
    {
        return new \stdClass;
    }

    public function disconnect()
    {
        $this->connection = null;
    }
}

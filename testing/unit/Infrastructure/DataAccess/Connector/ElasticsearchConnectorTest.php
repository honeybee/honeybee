<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ElasticsearchConnector;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Tests\TestCase;

class ElasticsearchConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name, ConfigInterface $config)
    {
        return new ElasticsearchConnector($name, $config);
    }
}

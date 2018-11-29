<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use GuzzleHttp\Client;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Infrastructure\DataAccess\Connector\GuzzleConnector;

class GuzzleConnectorTest extends ConnectorInterfaceTest
{
    public function testConnectorReturnsConfiguredGuzzleClient()
    {
        $connector = $this->getConnector('default', new ArrayConfig([]));
        $client = $connector->getConnection();
        $this->assertInstanceOf(Client::class, $client);
    }

    protected function getConnector($name, ConfigInterface $config)
    {
        return new GuzzleConnector($name, $config);
    }
}

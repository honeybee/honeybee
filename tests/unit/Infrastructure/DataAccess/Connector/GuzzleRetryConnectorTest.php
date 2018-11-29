<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use GuzzleHttp\Client;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\GuzzleRetryConnector;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;

class GuzzleRetryConnectorTest extends ConnectorInterfaceTest
{
    public function testConnectorReturnsConfiguredGuzzleClient()
    {
        $connector = $this->getConnector('retry', new ArrayConfig([]));
        $client = $connector->getConnection();
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConnectorHasRetryMiddlewareConfiguredAndEnabled()
    {
        $connector = $this->getConnector('retry', new ArrayConfig([]));
        $client = $connector->getConnection();
        $this->assertTrue($client->getConfig('retry_enabled'));
        $this->assertSame(2, $client->getConfig('max_retry_attempts'));
        $this->assertFalse($client->getConfig('retry_only_if_retry_after_header'));
        $this->assertFalse($client->getConfig('expose_retry_header'));
        $this->assertFalse($client->getConfig('retry_on_timeout'));
        $this->assertSame([429, 503], $client->getConfig('retry_on_status'));
        $this->assertSame(1.5, $client->getConfig('default_retry_multiplier'));
    }

    protected function getConnector($name, ConfigInterface $config)
    {
        return new GuzzleRetryConnector($name, $config);
    }
}

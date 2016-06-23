<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Infrastructure\DataAccess\Connector\ElasticsearchConnector;
use Honeybee\Tests\TestCase;
use Mockery;

class ElasticsearchConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name, ConfigInterface $config)
    {
        return new ElasticsearchConnector($name, $config);
    }

    public function testPingEndpointIsCalledOnStatus()
    {
        $mock_client = Mockery::mock(Client::CLASS);
        $mock_client->shouldReceive('ping')->once()->withNoArgs()->andReturn(true);

        $connector = Mockery::mock(
            ElasticsearchConnector::CLASS . '[getConnection]',
            ['connectorname', new ArrayConfig([])]
        );
        $connector->shouldReceive('getConnection')->once()->andReturn($mock_client);

        $status = $connector->getStatus();
        $this->assertTrue($status->isWorking());
        $this->assertEquals(['message' => 'Pinging elasticsearch succeeded.'], $status->getDetails());
    }
}

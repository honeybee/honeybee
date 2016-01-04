<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorService;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
use Honeybee\Infrastructure\DataAccess\Connector\StatusReport;
use Honeybee\Tests\TestCase;

class ConnectorServiceTest extends TestCase
{
    public function testCreateSucceeds()
    {
        $service = new ConnectorService(new ConnectorMap);
        $this->assertInstanceOf(ConnectorService::CLASS, $service);
    }

    public function testGetConnectorMapWorks()
    {
        $connector_map = new ConnectorMap;
        $connector_map->setItem('conn1', new TestConnector('conn1', new ArrayConfig([])));

        $service = new ConnectorService($connector_map);

        $this->assertSame($connector_map, $service->getConnectorMap());
    }

    public function testGetConnectorWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig([]));

        $connector_map = new ConnectorMap;
        $connector_map->setItem('conn1', $connector);

        $service = new ConnectorService($connector_map);

        $this->assertSame($connector, $service->getConnector('conn1'));
    }

    public function testGetConnectionWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig([]));

        $connector_map = new ConnectorMap;
        $connector_map->setItem('conn1', $connector);

        $service = new ConnectorService($connector_map);

        $this->assertInstanceOf('\\stdClass', $service->getConnection('conn1'));
    }

    public function testDisconnectWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig([]));

        $connector_map = new ConnectorMap;
        $connector_map->setItem('conn1', $connector);

        $service = new ConnectorService($connector_map);

        $this->assertFalse($connector->isConnected());
        $this->assertInstanceOf('\\stdClass', $service->getConnection('conn1'));
        $this->assertTrue($connector->isConnected());
        $service->disconnect('conn1');
        $this->assertFalse($connector->isConnected());
    }

    public function testGetStatusReportWorks()
    {
        $service = new ConnectorService(new ConnectorMap);
        $this->assertInstanceOf(StatusReport::CLASS, $service->getStatusReport());
    }
}

<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
use Honeybee\Tests\TestCase;

class ConnectorTest extends TestCase
{
    public function testGetNameWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig(['foo' => 'bar']));
        $this->assertSame('conn1', $connector->getName());
    }

    public function testGetConnectionWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig([]));

        $this->assertFalse($connector->isConnected());
        $connection = $connector->getConnection();
        $this->assertInstanceOf('\\stdClass', $connection);
        $this->assertTrue($connector->isConnected());
    }

    public function testDisconnectWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig([]));

        $this->assertFalse($connector->isConnected());
        $connector->getConnection();
        $this->assertTrue($connector->isConnected());
        $connector->disconnect();
        $this->assertFalse($connector->isConnected());
    }

    public function testGetConfigWorks()
    {
        $connector = new TestConnector('conn1', new ArrayConfig(['foo' => 'bar']));

        $this->assertInstanceOf(ConfigInterface::CLASS, $connector->getConfig());
        $this->assertSame('bar', $connector->getConfig()->get('foo'));
    }

    public function testCreatingDefaultConnectorWithUnknownStatusSucceeds()
    {
        $connector = new TestConnector('default', new ArrayConfig([]));
        $status = $connector->getStatus();
        $this->assertTrue($status->isUnknown());
        $this->assertSame('default', $status->getConnectionName());
    }

    public function testFakeFailingStatusSucceeds()
    {
        $connector = new TestConnector('failing', new ArrayConfig(['fake_status' => Status::FAILING]));
        $status = $connector->getStatus();
        $this->assertTrue($status->isFailing());
        $this->assertSame('failing', $status->getConnectionName());
    }

    public function testFakeWorkingStatusSucceeds()
    {
        $connector = new TestConnector('working', new ArrayConfig(['fake_status' => Status::WORKING]));
        $status = $connector->getStatus();
        $this->assertTrue($status->isWorking());
        $this->assertSame('working', $status->getConnectionName());
    }
}

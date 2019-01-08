<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use PHPUnit\Framework\TestCase;

/**
 * This abstract class may be used to write tests for your own connectors.
 *
 * @see Honeybee\Tests\Infrastructure\DataAccess\Connector\Flysystem\LocalConnectorTest
 */
abstract class ConnectorInterfaceTest extends TestCase
{
    abstract protected function getConnector($name, ConfigInterface $config);

    public function testGetNameWorks()
    {
        $connector = $this->getConnector('conn1', new ArrayConfig(['name' => 'foo']));
        $this->assertSame('conn1', $connector->getName());
    }

    public function testGetConnectionWorks()
    {
        $connector = $this->getConnector('conn1', new ArrayConfig([]));
        $connection = $connector->getConnection();
        $this->assertTrue($connector->isConnected(), 'Connector should be connected after getConnection() call');
        $this->assertTrue(is_object($connection), 'A getConnection() call should yield a client/connection object');
    }

    public function testDisconnectWorks()
    {
        $connector = $this->getConnector('conn1', new ArrayConfig([]));
        $connector->getConnection();
        $this->assertTrue($connector->isConnected());
        $connector->disconnect();
        $this->assertFalse($connector->isConnected());
    }

    public function testGetConfigWorks()
    {
        $connector = $this->getConnector('conn1', new ArrayConfig(['foo' => 'bar']));
        $this->assertInstanceOf(ConfigInterface::CLASS, $connector->getConfig());
        $this->assertSame('bar', $connector->getConfig()->get('foo'));
    }

    public function testFakingStatusAsFailingSucceeds()
    {
        $connector = $this->getConnector('failing', new ArrayConfig(['fake_status' => Status::FAILING]));
        $status = $connector->getStatus();
        $this->assertTrue($status->isFailing());
        $this->assertSame('failing', $status->getConnectionName());
    }

    public function testFakingStatusAsWorkingSucceeds()
    {
        $connector = $this->getConnector('working', new ArrayConfig(['fake_status' => Status::WORKING]));
        $status = $connector->getStatus();
        $this->assertTrue($status->isWorking());
        $this->assertSame('working', $status->getConnectionName());
    }
}

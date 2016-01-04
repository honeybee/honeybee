<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
use Honeybee\Tests\TestCase;

class StatusTest extends TestCase
{
    public function testCreatingUnknownStatusSucceeds()
    {
        $connector = new TestConnector('default', new ArrayConfig([]));

        $status = new Status($connector, Status::UNKNOWN);
        $this->assertTrue($status->isUnknown());
        $this->assertSame(Status::UNKNOWN, $status->getStatus());
        $this->assertSame('default', $status->getConnectionName());

        $status = Status::unknown($connector);
        $this->assertTrue($status->isUnknown());
        $this->assertSame(Status::UNKNOWN, $status->getStatus());
        $this->assertSame('default', $status->getConnectionName());
    }

    public function testCreatingFailingStatusSucceeds()
    {
        $connector = new TestConnector('failing', new ArrayConfig([]));

        $status = new Status($connector, Status::FAILING);
        $this->assertTrue($status->isFailing());
        $this->assertSame(Status::FAILING, $status->getStatus());
        $this->assertSame('failing', $status->getConnectionName());

        $status = Status::failing($connector);
        $this->assertTrue($status->isFailing());
        $this->assertSame(Status::FAILING, $status->getStatus());
        $this->assertSame('failing', $status->getConnectionName());
    }

    public function testFakeWorkingStatusConnectorSucceeds()
    {
        $connector = new TestConnector('working', new ArrayConfig([]));

        $status = new Status($connector, Status::WORKING);
        $this->assertTrue($status->isWorking());
        $this->assertSame(Status::WORKING, $status->getStatus());
        $this->assertSame('working', $status->getConnectionName());

        $status = Status::working($connector);
        $this->assertTrue($status->isWorking());
        $this->assertSame(Status::WORKING, $status->getStatus());
        $this->assertSame('working', $status->getConnectionName());
    }

    public function testJsonSerializationWorks()
    {
        $connector = new TestConnector('working', new ArrayConfig([]));
        $status = new Status($connector, Status::WORKING);

        $json = json_encode($status);
        $array = json_decode($json, true);

        $this->assertSame(Status::WORKING, $array['status']);
        $this->assertSame($status->getStatus(), $array['status']);

        $this->assertSame('working', $array['connection_name']);
        $this->assertSame($status->getConnectionName(), $array['connection_name']);

        $this->assertSame(TestConnector::CLASS, $array['implementor']);
        $this->assertSame($status->getImplementor(), $array['implementor']);

        $this->assertSame([], $array['details']);
        $this->assertSame($status->getDetails(), $array['details']);
    }
}

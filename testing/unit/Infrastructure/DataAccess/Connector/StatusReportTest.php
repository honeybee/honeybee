<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorMap;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
use Honeybee\Infrastructure\DataAccess\Connector\StatusReport;
use Honeybee\Tests\TestCase;

class StatusReportTest extends TestCase
{
    public function testCreationSucceeds()
    {
        $report1 = new StatusReport(Status::UNKNOWN, [], []);

        $report2 = StatusReport::generate(new ConnectorMap);
        $this->assertInstanceOf(StatusReport::CLASS, $report2);
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testCreationThrowsOnUnknownStatus()
    {
        new StatusReport('nonexistantstatus', [], []);
    } // @codeCoverageIgnore

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testCreationThrowsOnNonStringStatus()
    {
        new StatusReport([], [], []);
    } // @codeCoverageIgnore

    public function testReportToStringSucceeds()
    {
        $report = StatusReport::generate(new ConnectorMap);
        $this->assertSame(Status::WORKING, (string)$report, 'Empty connector map leads to WORKING status report');
    }

    public function testToStringIsCorrectForFailingReport()
    {
        $connector_map = new ConnectorMap;
        $connector_map->setItem(
            'failing',
            new TestConnector('failing', new ArrayConfig(['fake_status'=> Status::FAILING]))
        );

        $report = StatusReport::generate($connector_map);

        $this->assertSame(Status::FAILING, (string)$report, 'One failing connection leads to report failing');
    }

    public function testToStringIsCorrectForWorkingReport()
    {
        $connector_map = new ConnectorMap;
        $connector_map->setItem(
            'working',
            new TestConnector('working', new ArrayConfig(['fake_status'=> Status::WORKING]))
        );

        $report = StatusReport::generate($connector_map);

        $this->assertSame(Status::WORKING, (string)$report);
    }

    public function testReportJsonSerializationSucceeds()
    {
        $connector_map = new ConnectorMap;
        $connector1 = new TestConnector('connection1', new ArrayConfig([]));
        $connector_map->setItem('connection1', $connector1);
        $connector_map->setItem(
            'connection2',
            new TestConnector('connection2', new ArrayConfig(['fake_status'=> Status::WORKING]))
        );
        $connector_map->setItem(
            'connection3',
            new TestConnector('connection3', new ArrayConfig(['fake_status'=> Status::FAILING]))
        );

        $report = StatusReport::generate($connector_map);

        $json = json_encode($report);
        $array = json_decode($json, true);

        $expected_stats = [
            'overall' => 3,
            'failing' => 1,
            'working' => 1,
            'unknown' => 1
        ];

        $this->assertSame(Status::FAILING, $array['status']);
        $this->assertSame($expected_stats, $array['stats']);
        $this->assertArrayHasKey('connection1', $array['details']);
        $this->assertArrayHasKey('connection2', $array['details']);
        $this->assertArrayHasKey('connection3', $array['details']);
        $this->assertArraySubset($connector1->getStatus()->toArray(), $array['details']['connection1']);
    }
}

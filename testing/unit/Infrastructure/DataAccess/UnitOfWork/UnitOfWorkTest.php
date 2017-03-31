<?php

namespace Honeybee\Tests\DataAccess\UnitOfWork;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWork;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\AggregateRootEventListMap;
use Honeybee\Model\Event\EventStreamInterface;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\NullLogger;

class UnitOfWorkTest extends TestCase
{
    public function testCreate()
    {
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('mock_identifier');
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);

        $this->assertSame($mock_aggregate_root, $unitOfWork->create());
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testCreateWithTrackingConflict()
    {
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('mock_identifier');
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->twice()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);

        $unitOfWork->create();
        $unitOfWork->create();
    } // @codeCoverageIgnore

    public function testCheckout()
    {
        $ar_event_list = new AggregateRootEventList;
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('mock_identifier');
        $mock_aggregate_root->shouldReceive('reconstituteFrom')->once()->with($ar_event_list);
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_stream = Mockery::mock(EventStreamInterface::CLASS);
        $mock_event_stream->shouldReceive('getEvents')->once()->withNoArgs()->andReturn($ar_event_list);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_reader->shouldReceive('read')->once()->with('mock_identifier')->andReturn($mock_event_stream);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);

        $this->assertSame($mock_aggregate_root, $unitOfWork->checkout('mock_identifier'));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testCheckoutWithMissingIdentifier()
    {
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn('mock_identifier');
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_reader->shouldReceive('read')->once()->with('missing_identifier')->andReturnNull();
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);

        $unitOfWork->checkout('missing_identifier');
    } // @codeCoverageIgnore

    public function testCommitNoneTracked()
    {
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);

        $this->assertEquals(new AggregateRootEventListMap, $unitOfWork->commit());
    }

    public function testCommitNoEvents()
    {
        $ar_event_list = new AggregateRootEventList;
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->twice()->withNoArgs()->andReturn('mock_identifier');
        $mock_aggregate_root->shouldReceive('getUncomittedEvents')->once()->withNoArgs()
            ->andReturn($ar_event_list);
        $mock_aggregate_root->shouldReceive('markAsComitted')->once()->withNoArgs()->andReturnNull();
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);
        $unitOfWork->create();

        $ar_list_map = new AggregateRootEventListMap(['mock_identifier' => $ar_event_list]);
        $this->assertEquals($ar_list_map, $unitOfWork->commit());
    }

    public function testCommit()
    {
        $ar_event = Mockery::mock(AggregateRootEventInterface::CLASS);
        $ar_event_list = new AggregateRootEventList([$ar_event]);
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_aggregate_root->shouldReceive('getIdentifier')->twice()->withNoArgs()->andReturn('mock_identifier');
        $mock_aggregate_root->shouldReceive('getUncomittedEvents')->once()->withNoArgs()
            ->andReturn($ar_event_list);
        $mock_aggregate_root->shouldReceive('markAsComitted')->once()->withNoArgs()->andReturnNull();
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('createEntity')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_event_reader = Mockery::mock(StorageReaderInterface::CLASS);
        $mock_event_writer = Mockery::mock(StorageWriterInterface::CLASS);
        $mock_event_writer->shouldReceive('write')->once()->with($ar_event)->andReturnNull();

        $unitOfWork = $this->createUoW($mock_art, $mock_event_reader, $mock_event_writer);
        $unitOfWork->create();

        $ar_list_map = new AggregateRootEventListMap(['mock_identifier' => $ar_event_list]);
        $this->assertEquals($ar_list_map, $unitOfWork->commit());
    }

    public function rollbackTest()
    {
        $this->markTestIncomplete('Test when implemented');
    }

    private function createUoW($art, $reader, $writer)
    {
        return new UnitOfWork(new ArrayConfig([]), $art, $reader, $writer, new NullLogger);
    }
}

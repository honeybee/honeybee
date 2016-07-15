<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Storage\Elasticsearch;

use Elasticsearch\Connections\ConnectionInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionWriter;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionList;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\NullLogger;

class ProjectionWriterTest extends TestCase
{
    public function testWrite()
    {
        $expected = [
            'index' => 'test-index',
            'type' => 'test-type',
            'id' => 'test-identifier',
            'body' => [
                'test' => 'data',
                'sample' => [ 'value' ]
            ]
        ];

        $mock_projection = Mockery::mock(ProjectionInterface::CLASS);
        $mock_projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn($expected['id']);
        $mock_projection->shouldReceive('toArray')->once()->withNoArgs()->andReturn($expected['body']);

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        $mock_connection->shouldReceive('index')->once()->with($expected)->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn($expected['index']);
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn($expected['type']);

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->write($mock_projection));
    }

    public function testWriteWithCustomConfig()
    {
        $expected = [
            'index' => 'override-index',
            'type' => 'overrid-type',
            'id' => 'test-identifier',
            'body' => [
                'test' => 'data',
                'sample' => [ 'value' ]
            ],
            'refresh' => true,
            'option' => 'value'
        ];

        $mock_projection = Mockery::mock(ProjectionInterface::CLASS);
        $mock_projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn($expected['id']);
        $mock_projection->shouldReceive('toArray')->once()->withNoArgs()->andReturn($expected['body']);

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        $mock_connection->shouldReceive('index')->once()->with($expected)->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn('test-index');
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn('test-type');

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([
                'index' => $expected['index'],
                'type' => $expected['type'],
                'parameters' => [
                    'index' => [ 'refresh' => true, 'option' => 'value' ]
                ]
            ]),
            new NullLogger
        );

        $this->assertNull($projection_writer->write($mock_projection));
    }

    /**
     * @expectedException \Honeybee\Common\Error\RuntimeError
     */
    public function testWriteWithNull()
    {
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->write(null));
    }

    /**
     * @expectedException \Honeybee\Common\Error\RuntimeError
     */
    public function testWriteWithArray()
    {
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->write([ 'data' ]));
    }

    public function testDelete()
    {
        $expected = [
            'index' => 'test-index',
            'type' => 'test-type',
            'id' => 'delete-identifier'
        ];

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        // ES create index on delete expectations
        $get_data = $expected;
        $get_data['refresh'] = false;
        $mock_connection->shouldReceive('get')->once()->with($get_data)->andReturnNull();
        // end workaround expectations
        $mock_connection->shouldReceive('delete')->once()->with($expected)->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn($expected['index']);
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn($expected['type']);

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->delete($expected['id']));
    }

    public function testDeleteWithCustomConfig()
    {
        $expected = [
            'index' => 'override-index',
            'type' => 'override-type',
            'id' => 'delete-identifier',
            'refresh' => true,
            'option' => 'value'
        ];

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        // ES create index on delete expectations
        $get_data = $expected;
        $get_data['refresh'] = false;
        unset($get_data['option']);
        $mock_connection->shouldReceive('get')->once()->with($get_data)->andReturnNull();
        // end workaround expectations
        $mock_connection->shouldReceive('delete')->once()->with($expected)->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn('test-index');
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn('test-type');

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([
                'index' => 'override-index',
                'type' => 'override-type',
                'parameters' => [
                    'get' => [ 'refresh' => true ],
                    'delete' => [ 'refresh' => true, 'option' => 'value' ]
                ]
            ]),
            new NullLogger
        );

        $this->assertNull($projection_writer->delete($expected['id']));
    }

    public function testDeleteWithNull()
    {
        // Expected behaviour is to log and ignore invalid ids
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->delete(null));
    }

    public function testDeleteWithArray()
    {
        // Expected behaviour is to log and ignore invalid ids
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->delete([ 'id' => 'nope' ]));
    }

    public function testDeleteWithEmptyString()
    {
        // Expected behaviour is to log and ignore invalid ids
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->delete(''));
    }

    public function testWriteMany()
    {
        $projections = [
            'k-l' => [ 'identifier' => 'k-l', 'firstname' => 'Konrad', 'lastname' => 'Lorenz' ],
            'w-t' => [ 'identifier' => 'w-t', 'firstname' => 'Wilfred',  'lastname' => 'Thesiger' ]
        ];

        $mock_map = Mockery::mock(ProjectionMap::CLASS);
        $mock_map->shouldReceive('isEmpty')->once()->withNoArgs()->andReturn(false);
        $mock_map->shouldReceive('getSize')->once()->withNoArgs()->andReturn(2);
        $mock_map->shouldReceive('toArray')->once()->withNoArgs()->andReturn($projections);

        $expected = [
            'body' => [
                [ 'index' => [ '_index' => 'test-index', '_type' => 'test-type', '_id' => 'k-l' ] ],
                $projections['k-l'],
                [ 'index' => [ '_index' => 'test-index', '_type' => 'test-type', '_id' => 'w-t' ] ],
                $projections['w-t']
            ]
        ];

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        $mock_connection->shouldReceive('bulk')->once()
            ->with(Mockery::on(
                function (array $data) use ($expected) {
                    $this->assertEquals($expected, $data);
                    return true;
                }
            ))
            ->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn('test-index');
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn('test-type');

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->writeMany($mock_map));
    }

    public function testWriteManyOneProjection()
    {
        $expected = [
            'index' => 'test-index',
            'type' => 'test-type',
            'id' => 'test-identifier',
            'body' => [ 'test' => 'data' ]
        ];

        $mock_projection = Mockery::mock(ProjectionInterface::CLASS);
        $mock_projection->shouldReceive('getIdentifier')->once()->withNoArgs()->andReturn($expected['id']);
        $mock_projection->shouldReceive('toArray')->once()->withNoArgs()->andReturn($expected['body']);

        // expectation is to redirect to write on single projection
        $mock_map = Mockery::mock(ProjectionMap::CLASS);
        $mock_list = Mockery::mock(ProjectionList::CLASS);
        $mock_map->shouldReceive('isEmpty')->once()->withNoArgs()->andReturn(false);
        $mock_map->shouldReceive('getSize')->once()->withNoArgs()->andReturn(1);
        $mock_map->shouldReceive('toList')->once()->withNoArgs()->andReturn($mock_list);
        $mock_list->shouldReceive('getFirst')->once()->withNoArgs()->andReturn($mock_projection);

        $mock_connection = Mockery::mock(ConnectionInterface::CLASS);
        $mock_connection->shouldReceive('index')->once()->with($expected)->andReturnNull();

        $mock_config = Mockery::mock(SettingsInterface::CLASS);
        $mock_config->shouldReceive('get')->once()->with('index')->andReturn($expected['index']);
        $mock_config->shouldReceive('get')->once()->with('type')->andReturn($expected['type']);

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldReceive('getConnection')->once()->withNoArgs()->andReturn($mock_connection);
        $mock_connector->shouldReceive('getConfig')->twice()->andReturn($mock_config);

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->writeMany($mock_map));
    }

    public function testWriteManyWithEmptyList()
    {
        $mock_map = Mockery::mock(ProjectionMap::CLASS);
        $mock_map->shouldReceive('isEmpty')->once()->withNoArgs()->andReturn(true);
        $mock_map->shouldNotReceive('toArray');

        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->writeMany($mock_map));
    }

    /**
     * @expectedException \Honeybee\Common\Error\RuntimeError
     */
    public function testWriteManyWithNull()
    {
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->writeMany(null));
    }

    /**
     * @expectedException \Honeybee\Common\Error\RuntimeError
     */
    public function testWriteManyWithArray()
    {
        $mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $mock_connector->shouldNotReceive('getConnection');

        $projection_writer = new ProjectionWriter(
            $mock_connector,
            new ArrayConfig([]),
            new NullLogger
        );

        $this->assertNull($projection_writer->writeMany([ 'a', 'b' ]));
    }
}

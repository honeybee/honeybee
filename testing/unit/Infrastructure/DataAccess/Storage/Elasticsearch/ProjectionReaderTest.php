<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Storage\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionReader;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Tests\Fixture\BookSchema\Projection\Book\BookType;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\NullLogger;
use Workflux\StateMachine\StateMachineInterface;

class ProjectionReaderTest extends TestCase
{
    protected $projection_type_map;

    protected $mock_connector;

    protected $mock_client;

    public function setUp()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);
        $book_type = new BookType($state_machine);
        $author_type = new AuthorType($state_machine);
        $this->projection_type_map = new ProjectionTypeMap([
            $book_type->getVariantPrefix() => $book_type,
            $author_type->getVariantPrefix() => $author_type
        ]);

        $this->mock_connector = Mockery::mock(ConnectorInterface::CLASS);
        $this->mock_client = Mockery::mock(Client::CLASS);
    }

    public function testReadAll()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_01.php');
        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'size' => 10,
            'body' => [ 'query' => [ 'match_all' => [] ] ]
        ])->andReturn($test_data['raw_result']);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $this->assertEquals($projections, $projection_reader->readAll());
    }

    public function testReadAllNoResults()
    {
        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'size' => 20,
            'body' => [ 'query' => [ 'match_all' => [] ] ]
        ])->andReturn([ 'hits' => [ 'total' => 0, 'hits' => [] ] ]);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $this->assertEquals([], $projection_reader->readAll(new Settings([ 'limit' => 20 ])));
    }

    public function testReadAllMixedResults()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_02.php');
        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')->once()->with([
            'index' => null,
            'type' => 'type',
            'size' => 5,
            'body' => [ 'query' => [ 'match_all' => [] ] ]
        ])->andReturn($test_data['raw_result']);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'type' => 'type', 'limit' => 5 ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $this->assertEquals($projections, $projection_reader->readAll());
    }

    public function testRead()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_03.php');
        $identifier = 'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1';

        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => null,
            'id' => $identifier
        ])->andReturn($test_data['raw_result']);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections([ $test_data['raw_result'] ]);
        $this->assertEquals($projections[0], $projection_reader->read($identifier));
    }

    /**
     * @expectedException Trellis\Common\Error\RuntimeException
     */
    public function testReadUnknownProjectionType()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_03.php');
        $identifier = 'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1';

        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => null,
            'id' => $identifier
        ])->andReturn($test_data['invalid_result']);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projection_reader->read($identifier);
    }

    public function testReadMissing()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_03.php');
        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => 'type1,type2',
            'id' => 'missing'
        ])->andThrow(Missing404Exception::CLASS);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type1,type2' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $this->assertNull($projection_reader->read('missing'));
    }

    public function testGetIterator()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_reader_test_01.php');
        $this->mock_connector->shouldReceive('getConfig')->twice()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'size' => 10,
            'body' => [ 'query' => [ 'match_all' => [] ] ]
        ])->andReturn($test_data['raw_result']);

        $projection_reader = new ProjectionReader(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type']),
            new NullLogger,
            $this->projection_type_map
        );

        $iterator = $projection_reader->getIterator();
        $this->assertInstanceOf(StorageReaderIterator::CLASS, $iterator);
        $this->assertTrue($iterator->valid());
    }

    protected function createProjections(array $results)
    {
        $projections = [];
        foreach ($results as $result) {
            $projections[] = $this->projection_type_map
                ->getItem($result['_source']['@type'])
                ->createEntity($result['_source']);
        }
        return $projections;
    }
}

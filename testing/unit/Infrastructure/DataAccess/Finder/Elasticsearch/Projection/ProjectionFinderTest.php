<?php

namespace Honeybee\Tests\DataAccess\Finder\Elasticsearch\Projection;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\Projection\ProjectionFinder;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Tests\Fixture\BookSchema\Projection\Author\AuthorType;
use Honeybee\Tests\Fixture\BookSchema\Projection\Book\BookType;
use Honeybee\Tests\TestCase;
use Psr\Log\NullLogger;
use Mockery;
use Workflux\StateMachine\StateMachineInterface;

class ProjectionFinderTest extends TestCase
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

    public function testGetByIdentifier()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_01.php');
        $identifier = 'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1';

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'id' => $identifier
        ])->andReturn($test_data['raw_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections([ $test_data['raw_result'] ]);
        $finder_result = new FinderResult($projections, 1);

        $this->assertEquals($finder_result, $projection_finder->getByIdentifier($identifier));
    }

    public function testGetByIdentifierMissing()
    {
        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'key' => 'value',
            'id' => 'missing'
        ])->andThrow(Missing404Exception::CLASS);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([
                'index' => 'index',
                'type' => 'type',
                'parameters' => [ 'get' => [ 'key' => 'value' ] ]
            ]),
            new NullLogger,
            $this->projection_type_map
        );

        $finder_result = new FinderResult([], 0);

        $this->assertEquals($finder_result, $projection_finder->getByIdentifier('missing'));
    }

    /**
     * @expectedException Trellis\Common\Error\RuntimeException
     */
    public function testGetByIdentifierUnknownProjectionType()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_01.php');
        $identifier = 'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1';

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('get')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'id' => $identifier
        ])->andReturn($test_data['invalid_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projection_finder->getByIdentifier($identifier);
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetByIdentifierMissingIndex()
    {
        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projection_finder->getByIdentifier('id1');
    }

    public function testGetByIdentifiers()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_02.php');
        $identifiers = [
            'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'honeybee-cmf.projection_fixtures.book-61d8da68-0d56-4b8b-b393-21f1a650d092-de_DE-1'
        ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('mget')->once()->with([
            'index' => 'index',
            'type' => 'type',
            'body' => [
                'ids' => $identifiers
            ]
        ])->andReturn($test_data['raw_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['docs']);
        $finder_result = new FinderResult($projections, 2);

        $this->assertEquals($finder_result, $projection_finder->getByIdentifiers($identifiers));
    }

    public function testGetByIdentifiersPartial()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_03.php');
        $identifiers = [
            'honeybee-cmf.projection_fixtures.book-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'honeybee-cmf.projection_fixtures.book-61d8da68-0d56-4b8b-b393-21f1a650d092-de_DE-1'
        ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('mget')->once()->with([
            'index' => 'index',
            'key' => 'value',
            'body' => [
                'ids' => $identifiers
            ]
        ])->andReturn($test_data['raw_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([
                'index' => 'index',
                'parameters' => [ 'mget' => ['key' => 'value' ] ]
            ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections([ $test_data['raw_result']['docs'][0] ]);
        $finder_result = new FinderResult($projections, 1);

        $this->assertEquals($finder_result, $projection_finder->getByIdentifiers($identifiers));
    }

    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testGetByIdentifiersMissingIndex()
    {
        $identifiers = [ 'id1', 'id2' ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projection_finder->getByIdentifiers($identifiers);
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testGetByIdentifiersMissingIds()
    {
        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projection_finder->getByIdentifiers([]);
    }

    public function testFind()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_04.php');
        $query = [ 'from' => 0, 'size' => 10, 'body' => [ 'query' => [ 'match_all' => [] ] ] ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')
            ->once()
            ->with(array_merge($query, [ 'index' => 'index', 'type' => 'type' ]))
            ->andReturn($test_data['raw_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $finder_result = new FinderResult($projections, 2);

        $this->assertEquals($finder_result, $projection_finder->find($query));
    }

    public function testFindMixedResults()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_05.php');
        $query = [ 'from' => 0, 'size' => 10, 'body' => [ 'query' => [ 'match_all' => [] ] ] ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')
        ->once()
        ->with(array_merge($query, [ 'index' => '_all', 'type' => 'type1,type2' ]))
        ->andReturn($test_data['raw_result']);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'type' => 'type1,type2' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $finder_result = new FinderResult($projections, 2);

        $this->assertEquals($finder_result, $projection_finder->find($query));
    }

    public function testFindNoResults()
    {
        $query = [ 'from' => 0, 'size' => 10, 'body' => [ 'query' => [ 'match_all' => [] ] ] ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')
            ->once()
            ->with(array_merge($query, [ 'index' => 'index', 'type' => 'type' ]))
            ->andReturn([ 'hits' => [ 'total' => 0, 'hits' => [] ] ]);

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $finder_result = new FinderResult([]);

        $this->assertEquals($finder_result, $projection_finder->find($query));
    }

    public function testScrollStart()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_04.php');
        $query = [ 'from' => 0, 'size' => 10, 'body' => [ 'query' => [ 'match_all' => [] ] ] ];

        $this->mock_connector->shouldReceive('getConfig')->once()->andReturn(new ArrayConfig([]));
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('search')
            ->once()
            ->with(array_merge(
                $query,
                [
                    'index' => 'index',
                    'type' => 'type',
                    'scroll' => '1m',
                    'sort' => [ '_doc' ],
                    'size' => 10
                ]
            ))
            ->andReturn(array_merge($test_data['raw_result'], [ '_scroll_id' => 'test_scroll_id' ]));

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $finder_result = new FinderResult($projections, 2, 0, 'test_scroll_id');

        $this->assertEquals($finder_result, $projection_finder->scrollStart($query));
    }

    public function testScrollNext()
    {
        $test_data = include(__DIR__ . '/Fixture/projection_finder_test_04.php');

        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('scroll')
            ->once()
            ->with([ 'scroll' => '1m', 'scroll_id' => 'test_scroll_id' ])
            ->andReturn(array_merge($test_data['raw_result'], [ '_scroll_id' => 'next_scroll_id' ]));

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $projections = $this->createProjections($test_data['raw_result']['hits']['hits']);
        $finder_result = new FinderResult($projections, 2, 0, 'next_scroll_id');

        $this->assertEquals($finder_result, $projection_finder->scrollNext('test_scroll_id'));
    }

    public function testScrollEnd()
    {
        $this->mock_connector->shouldReceive('getConnection')->once()->andReturn($this->mock_client);
        $this->mock_client->shouldReceive('clearScroll')
            ->once()
            ->with([ 'scroll_id' => 'last_scroll_id' ])
            ->andReturnNull();

        $projection_finder = new ProjectionFinder(
            $this->mock_connector,
            new ArrayConfig([ 'index' => 'index', 'type' => 'type' ]),
            new NullLogger,
            $this->projection_type_map
        );

        $this->assertNull($projection_finder->scrollEnd('last_scroll_id'));
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

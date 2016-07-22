<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\DataAccess\Finder\FinderInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Infrastructure\DataAccess\Query\StoredQueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\ProjectionQueryService;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Psr\Log\NullLogger;
use Mockery;

class ProjectionQueryServiceTest extends TestCase
{
    public function testFindByIdentifier()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('getByIdentifier')->once()->with('123')->andReturn($mock_result);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [ 'default' => [ 'finder' => $mock_finder ] ],
            new NullLogger
        );

        $this->assertEquals($mock_result, $projection_service->findByIdentifier('123'));
    }

    public function testFindByIdentifierWithMapping()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('getByIdentifier')->once()->with('123')->andReturn($mock_result);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [ 'test_mapping' => [ 'finder' => $mock_finder ] ],
            new NullLogger
        );

        $this->assertEquals($mock_result, $projection_service->findByIdentifier('123', 'test_mapping'));
    }

    public function testFindByIdentifiers()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('getByIdentifiers')->once()->with(['123', '234'])->andReturn($mock_result);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [ 'default' => [ 'finder' => $mock_finder ] ],
            new NullLogger
        );

        $this->assertEquals($mock_result, $projection_service->findByIdentifiers(['123', '234']));
    }

    public function testFindByIdentifiersWithMapping()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('getByIdentifiers')->once()->with(['123', '234'])->andReturn($mock_result);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [ 'test_mapping' => [ 'finder' => $mock_finder ] ],
            new NullLogger
        );

        $this->assertEquals($mock_result, $projection_service->findByIdentifiers(['123', '234'], 'test_mapping'));
    }

    public function testFind()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('find')->once()->with([ 'translated' => 'query' ])->andReturn($mock_result);

        $mock_query_translation = Mockery::mock(QueryTranslationInterface::CLASS);
        $mock_query_translation
            ->shouldReceive('translate')
            ->once()
            ->with(Mockery::type(QueryInterface::CLASS))
            ->andReturn([ 'translated' => 'query' ]);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [
                'default' => [
                    'finder' => $mock_finder,
                    'query_translation' => $mock_query_translation
                ]
            ],
            new NullLogger
        );

        $mock_query = Mockery::mock(QueryInterface::CLASS);
        $this->assertEquals($mock_result, $projection_service->find($mock_query));
    }

    public function testFindWithMapping()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder->shouldReceive('find')->once()->with([ 'translated' => 'query' ])->andReturn($mock_result);

        $mock_query_translation = Mockery::mock(QueryTranslationInterface::CLASS);
        $mock_query_translation
            ->shouldReceive('translate')
            ->once()
            ->with(Mockery::type(QueryInterface::CLASS))
            ->andReturn([ 'translated' => 'query' ]);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [
                'test_mapping' => [
                    'finder' => $mock_finder,
                    'query_translation' => $mock_query_translation
                ]
            ],
            new NullLogger
        );

        $mock_query = Mockery::mock(QueryInterface::CLASS);
        $this->assertEquals($mock_result, $projection_service->find($mock_query, 'test_mapping'));
    }

    public function testFindWithStoredQueryMapping()
    {
        $mock_result = new FinderResult;
        $mock_finder = Mockery::mock(FinderInterface::CLASS);
        $mock_finder
            ->shouldReceive('findByStored')
            ->once()
            ->with([ 'translated' => 'query' ])
            ->andReturn($mock_result);

        $mock_query_translation = Mockery::mock(StoredQueryTranslationInterface::CLASS);
        $mock_query_translation
            ->shouldReceive('translate')
            ->once()
            ->with(Mockery::type(StoredQueryInterface::CLASS))
            ->andReturn([ 'translated' => 'query' ]);

        $projection_service = new ProjectionQueryService(
            new ArrayConfig([]),
            [
                'test_mapping' => [
                    'finder' => $mock_finder,
                    'query_translation' => $mock_query_translation
                ]
            ],
            new NullLogger
        );

        $mock_query = Mockery::mock(StoredQueryInterface::CLASS);
        $this->assertEquals($mock_result, $projection_service->find($mock_query, 'test_mapping'));
    }

    public function testWalkResources()
    {
        $this->markTestIncomplete('Add tests for walkResources method.');
    }
}

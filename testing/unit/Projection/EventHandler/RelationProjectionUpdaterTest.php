<?php

namespace Honeybee\Tests\Projection\EventHandler;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionWriter;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\Event\Bus\EventBus;
use Honeybee\Projection\EventHandler\RelationProjectionUpdater;
use Honeybee\Projection\Event\ProjectionUpdatedEvent;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Tests\Fixture\GameSchema\Projection\Game\GameType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\PlayerType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType;
use Honeybee\Tests\Fixture\TopicSchema\Projection\TopicOption\TopicOptionType;
use Honeybee\Tests\Fixture\TopicSchema\Projection\Topic\TopicType;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\NullLogger;

class RelationProjectionUpdaterTest extends TestCase
{
    protected $projection_type_map;

    public function setUp()
    {
        $game_type = new GameType();
        $player_type = new PlayerType();
        $team_type = new TeamType();
        $topic_type = new TopicType();
        $topic_option_type = new TopicOptionType();
        $this->projection_type_map = new ProjectionTypeMap(
            [
                $game_type->getVariantPrefix() => $game_type,
                $player_type->getVariantPrefix() => $player_type,
                $team_type->getVariantPrefix() => $team_type,
                $topic_type->getVariantPrefix() => $topic_type,
                $topic_option_type->getVariantPrefix() => $topic_option_type
            ]
        );
    }

    /**
     * @dataProvider provideTestUpdateEvents
     */
    public function testHandleUpdateEvents(array $event, array $query, array $projections, array $expectations)
    {
        // build projection finder results
        foreach ($projections as $projection) {
            $projection_type = $this->projection_type_map->getItem($projection['@type']);
            $projection_type_prefix = $projection_type->getPrefix();
            $projection_type_variant_prefix = $projection_type->getVariantPrefix();
            $related_projections[] = $projection_type->createEntity($projection);
        }

        // prepare mock query responses
        $mock_query_service = Mockery::mock(QueryServiceInterface::CLASS);
        $mock_query_service_map = Mockery::mock(QueryServiceMap::CLASS);
        $service_name = $projection_type_variant_prefix . '::query_service';
        $mock_query_service_map->shouldReceive('getItem')->once()->with($service_name)->andReturn($mock_query_service);
        $mock_query_service->shouldReceive('scroll')
            ->once()
            ->with(
                Mockery::on(
                    function (QueryInterface $search_query) use ($query) {
                        $this->assertEquals($query, $search_query->toArray());
                        return true;
                    }
                ),
                Mockery::on(
                    function (\Closure $callback) use ($related_projections) {
                        foreach ($related_projections as $index => $projection) {
                            $callback($projection, $index);
                        }
                        return true;
                    }
                )
            )
            ->andReturnNull();

        // prepare storage writer and event bus expectations
        $mock_event_bus = Mockery::mock(EventBus::CLASS);
        $mock_storage_writer = Mockery::mock(ProjectionWriter::CLASS);
        $mock_storage_writer_map = Mockery::mock(StorageWriterMap::CLASS);
        $mock_storage_writer_map->shouldReceive('getItem')
            ->once()
            ->with($projection_type_variant_prefix . '::view_store::writer')
            ->andReturn($mock_storage_writer);

        if (!empty($expectations)) {
            $mock_storage_writer->shouldReceive('writeMany')
                ->once()
                ->with(Mockery::on(
                    function (ProjectionMap $projection_map) use ($expectations) {
                        $this->assertEquals($expectations, array_values($projection_map->toArray()));
                        return true;
                    }
                ));

            foreach ($expectations as $expectation) {
                $mock_event_bus->shouldReceive('distribute')
                    ->once()
                    ->with('honeybee.events.infrastructure', Mockery::on(
                        function (ProjectionUpdatedEvent $update_event) use ($expectation) {
                            $this->assertEquals($expectation['identifier'], $update_event->getProjectionIdentifier());
                            $this->assertEquals($expectation['@type'], $update_event->getProjectionType());
                            $this->assertEquals($expectation, $update_event->getData());
                            return true;
                        }
                    ));
            }
        } else {
            $mock_event_bus->shouldNotReceive('distribute');
            $mock_storage_writer->shouldReceive('writeMany')
                ->once()
                ->with(Mockery::on(
                    function (ProjectionMap $projection_map) {
                        $this->assertCount(0, $projection_map);
                        return true;
                    }
                ));
        }

        // prepare and test subject
        $relation_projection_updater = new RelationProjectionUpdater(
            new ArrayConfig([ 'projection_type' => $projection_type_variant_prefix ]),
            new NullLogger,
            $mock_storage_writer_map,
            $mock_query_service_map,
            $this->projection_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent($event);
        $relation_projection_updater->handleEvent($event);
    }

    public function testHandleCreateEvents()
    {
        // Relation projection updater should not handle creation events
        $mock_storage_writer_map = Mockery::mock(StorageWriterMap::CLASS)->shouldNotReceive('getItem')->mock();
        $mock_query_service_map = Mockery::mock(QueryServiceMap::CLASS)->shouldNotReceive('getItem')->mock();
        $mock_event_bus = Mockery::mock(EventBus::CLASS)->shouldNotReceive('distribute')->mock();

        // prepare and test subject
        $relation_projection_updater = new RelationProjectionUpdater(
            new ArrayConfig([]),
            new NullLogger,
            $mock_storage_writer_map,
            $mock_query_service_map,
            $this->projection_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent([
            '@type' => 'Honeybee\Projection\Event\ProjectionCreatedEvent',
            'uuid' => '44c4597c-f463-4916-a330-2db87ef36547',
            'projection_type' => 'honeybee_tests.game_schema.player::projection.standard',
            'projection_identifier' => 'honeybee.fixtures.player-a726301d-dbae-4fb6-91e9-a19188a17e71-de_DE-1',
            'data' => []
        ]);
        $relation_projection_updater->handleEvent($event);
    }

    // ------------------------------ helpers ------------------------------

    /**
     * @codeCoverageIgnore
     */
    public function provideTestUpdateEvents()
    {
        $tests = [];
        foreach (glob(__DIR__ . '/Fixture/relation_projection_update_test*.php') as $filename) {
            $tests[] = include $filename;
        }
        return $tests;
    }

    protected function buildEvent(array $event_state)
    {
        $event_type_class = $event_state['@type'];
        return new $event_type_class($event_state);
    }
}

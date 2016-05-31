<?php

namespace Honeybee\Tests\Projection\EventHandler;

use Honeybee\Tests\TestCase;
use Honeybee\Model\Event\EmbeddedEntityEventList;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Projection\EventHandler\RelationProjectionUpdater;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionUpdatedEvent;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionWriter;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBus;
use Honeybee\Tests\Fixture\GameSchema\Projection\Game\GameType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Player\PlayerType;
use Honeybee\Tests\Fixture\GameSchema\Projection\Team\TeamType;
use Workflux\StateMachine\StateMachineInterface;
use Psr\Log\NullLogger;
use Mockery;

class RelationProjectionUpdaterTest extends TestCase
{
    protected $aggregate_root_type_map;

    protected $projection_type_map;

    public function setUp()
    {
        $state_machine = Mockery::mock(StateMachineInterface::CLASS);

        $game_type = new GameType($state_machine);
        $player_type = new PlayerType($state_machine);
        $team_type = new TeamType($state_machine);
        $this->projection_type_map = new ProjectionTypeMap(
            [
                $game_type->getPrefix() => $game_type,
                $player_type->getPrefix() => $player_type,
                $team_type->getPrefix() => $team_type
            ]
        );
    }

    /**
     * @dataProvider provideTestEvents
     */
    public function testHandleEvents(array $event, array $query, array $projections, array $expectations)
    {
        // build projection finder results
        foreach ($projections as $projection) {
            $projection_type = $this->projection_type_map->getByEntityImplementor($projection['@type']);
            $projection_type_prefix = $projection_type->getPrefix();
            $related_projections[] = $projection_type->createEntity($projection);
        }

        // prepare mock query responses
        $mock_query_service = Mockery::mock(QueryServiceInterface::CLASS);
        $mock_query_service_map = Mockery::mock(QueryServiceMap::CLASS);
        $mock_finder_result = Mockery::mock(FinderResult::CLASS);
        $mock_finder_result->shouldReceive('getResults')->once()->withNoArgs()->andReturn($related_projections);
        $service_name = $projection_type_prefix . '::query_service';
        $mock_query_service_map->shouldReceive('getItem')->once()->with($service_name)->andReturn($mock_query_service);
        $mock_query_service->shouldReceive('find')
            ->once()
            ->with(Mockery::on(
                function (QueryInterface $search_query) use ($query) {
                    $this->assertEquals($query, $search_query->toArray());
                    return true;
                }
            ))
            ->andReturn($mock_finder_result);

        // prepare storage writer and event bus expectations
        $mock_event_bus = Mockery::mock(EventBus::CLASS);
        $mock_storage_writer = Mockery::mock(ProjectionWriter::CLASS);
        $mock_storage_writer_map = Mockery::mock(StorageWriterMap::CLASS);
        if (!empty($expectations) && $expectations !== $projections) {
            $store_name = $projection_type_prefix . '::projection.standard::view_store::writer';
            $mock_storage_writer_map->shouldReceive('getItem')
                ->times(count($expectations))
                ->with($store_name)
                ->andReturn($mock_storage_writer);

            foreach ($expectations as $expectation) {
                $mock_storage_writer->shouldReceive('write')
                    ->once()
                    ->with(Mockery::on(
                        function (ProjectionInterface $projection) use ($expectation) {
                            $this->assertEquals($expectation, $projection->toArray());
                            return true;
                        }
                    ))
                    ->andReturnNull();

                $mock_event_bus->shouldReceive('distribute')
                    ->once()
                    ->with('honeybee.events.infrastructure', Mockery::on(
                        function (ProjectionUpdatedEvent $update_event) use ($expectation) {
                            $this->assertEquals($expectation['identifier'], $update_event->getProjectionIdentifier());
                            $this->assertEquals($expectation['@type'] . 'Type', $update_event->getProjectionType());
                            $this->assertEquals($expectation, $update_event->getData());
                            return true;
                        }
                    ))
                    ->andReturnNull();
            }
        } else {
            $mock_storage_writer_map->shouldNotReceive('getItem');
            $mock_storage_writer->shouldNotReceive('write');
            $mock_event_bus->shouldNotReceive('distribute');
        }

        // prepare and test subject
        $relation_projection_updater = new RelationProjectionUpdater(
            new ArrayConfig([ 'projection_type' => $projection_type_prefix ]),
            new NullLogger,
            $mock_storage_writer_map,
            $mock_query_service_map,
            $this->projection_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent($event);
        $relation_projection_updater->handleEvent($event);
    }

    // ------------------------------ helpers ------------------------------

    /**
     * @codeCoverageIgnore
     */
    public function provideTestEvents()
    {
        $tests = [];
        foreach (glob(__DIR__ . '/Fixture/relation_projection_updater*.php') as $filename) {
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

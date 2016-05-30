<?php

namespace Honeybee\Tests\Projection\EventHandler;

use Honeybee\Tests\TestCase;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Event\EmbeddedEntityEventList;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionUpdatedEvent;
use Honeybee\Projection\EventHandler\ProjectionUpdater;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResultInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionWriter;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection\ProjectionReader;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Tests\Fixtures\GameSchema\Model\Game\GameType;
use Honeybee\Tests\Fixtures\GameSchema\Model\Team\TeamType;
use Honeybee\Tests\Fixtures\GameSchema\Projection\Game\GameType as GameProjectionType;
use Honeybee\Tests\Fixtures\GameSchema\Projection\Player\PlayerType as PlayerProjectionType;
use Honeybee\Tests\Fixtures\GameSchema\Projection\Team\TeamType as TeamProjectionType;
use Workflux\StateMachine\StateMachineInterface;
use Psr\Log\NullLogger;
use Mockery as M;

class ProjectionUpdaterTest extends TestCase
{
    protected $aggregate_root_type_map;

    protected $projection_type_map;

    public function setUp()
    {
        $state_machine =  M::mock(StateMachineInterface::CLASS);

        $game_aggregate_root_type = new GameType($state_machine);
        $team_aggregate_root_type = new TeamType($state_machine);
        $this->aggregate_root_type_map = new AggregateRootTypeMap(
            [
                $game_aggregate_root_type->getPrefix() => $game_aggregate_root_type,
                $team_aggregate_root_type->getPrefix() => $team_aggregate_root_type
            ]
        );

        $game_projection_type = new GameProjectionType($state_machine);
        $player_projection_type = new PlayerProjectionType($state_machine);
        $team_projection_type = new TeamProjectionType($state_machine);
        $this->projection_type_map = new ProjectionTypeMap(
            [
                $game_projection_type->getPrefix() => $game_projection_type,
                $player_projection_type->getPrefix() => $player_projection_type,
                $team_projection_type->getPrefix() => $team_projection_type
            ]
        );
    }

    /**
     * @dataProvider provideTestCreatedEvents
     */
    public function testHandleEventsCreatedEvent(array $event_state, array $projections, array $expectations)
    {
        $mock_finder_result = M::mock(FinderResultInterface::CLASS);
        $this->addProjectionsToMockFinderResult($mock_finder_result, $projections);

        $mock_finder = M::mock(FinderInterface::CLASS);
        $this->addEmdeddedEventsToMockFinder(
            $mock_finder,
            $event_state['embedded_entity_events'],
            $mock_finder_result
        );

        $mock_storage_writer = M::mock(ProjectionWriter::CLASS);
        $this->addExpectationsToStorageWriter($mock_storage_writer, $expectations);

        $mock_event_bus = M::mock(EventBusInterface::CLASS);
        $this->addExpectationsToEventBus($mock_event_bus, $expectations);

        $mock_data_access_service = M::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('getStorageWriter')->once()->andReturn($mock_storage_writer);
        $mock_data_access_service->shouldReceive('getFinder')->times(count($projections))->andReturn($mock_finder);

        $mock_query_service_map = M::mock(QueryServiceMap::CLASS);

        // prepare and execute tests
        $projection_updater = new ProjectionUpdater(
            new ArrayConfig([]),
            new NullLogger,
            $mock_data_access_service,
            $mock_query_service_map,
            $this->projection_type_map,
            $this->aggregate_root_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent($event_state);
        $projection_updater->handleEvent($event);
    }

    /**
     * @dataProvider provideTestModifiedEvents
     */
    public function testHandleEventsModifiedEvent(
        array $event_state,
        array $subject,
        array $projections,
        array $expectations
    ) {
        $mock_finder_result = M::mock(FinderResultInterface::CLASS);
        $this->addProjectionsToMockFinderResult($mock_finder_result, $projections);

        $mock_finder = M::mock(FinderInterface::CLASS);
        $this->addEmdeddedEventsToMockFinder(
            $mock_finder,
            $event_state['embedded_entity_events'],
            $mock_finder_result
        );

        $mock_storage_writer = M::mock(ProjectionWriter::CLASS);
        $this->addExpectationsToStorageWriter($mock_storage_writer, $expectations);

        $mock_event_bus = M::mock(EventBusInterface::CLASS);
        $this->addExpectationsToEventBus($mock_event_bus, $expectations);

        $mock_data_access_service = M::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('getStorageWriter')->once()->andReturn($mock_storage_writer);
        $mock_data_access_service->shouldReceive('getFinder')->times(count($projections))->andReturn($mock_finder);

        // expectations for loading subject
        $subject = $this->createProjection($subject);
        $mock_storage_reader = M::mock(ProjectionReader::CLASS);
        $mock_storage_reader->shouldReceive('read')->once()->with($subject->getIdentifier())->andReturn($subject);
        $mock_data_access_service->shouldReceive('getStorageReader')
            ->once()
            ->with($subject->getType()->getPrefix() . '::projection.standard::view_store::reader')
            ->andReturn($mock_storage_reader);

        $mock_query_service_map = M::mock(QueryServiceMap::CLASS);

        // prepare and execute tests
        $projection_updater = new ProjectionUpdater(
            new ArrayConfig([]),
            new NullLogger,
            $mock_data_access_service,
            $mock_query_service_map,
            $this->projection_type_map,
            $this->aggregate_root_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent($event_state);
        $projection_updater->handleEvent($event);
    }

    /**
     * @dataProvider provideTestNodeMovedEvents
     */
    public function testHandleEventsNodeMovedEvent(
        array $event_state,
        array $subject,
        array $parent,
        array $query,
        array $projections,
        array $expectations
    ) {
        $mock_finder_result = M::mock(FinderResultInterface::CLASS);
        foreach ($projections as $projection) {
            $related_projections[] = $this->createProjection($projection);
        }
        $mock_finder_result->shouldReceive('getResults')->once()->andReturn($related_projections);

        // query execution expectations for moved nodes
        $mock_query_service_map = M::mock(QueryServiceMap::CLASS);
        $mock_query_service = M::mock(QueryServiceInterface::CLASS);
        $mock_query_service_map->shouldReceive('getItem')
            ->once()
            ->with('honeybee-tests.game_schema.team::query_service')
            ->andReturn($mock_query_service);
        $mock_query_service->shouldReceive('find')
            ->once()
            ->with(M::on(
                function (QueryInterface $search_query) use ($query) {
                    $this->assertEquals($query, $search_query->toArray());
                    return true;
                }
            ))
            ->andReturn($mock_finder_result);

        $mock_finder = M::mock(FinderInterface::CLASS);
        $this->addEmdeddedEventsToMockFinder(
            $mock_finder,
            $event_state['embedded_entity_events'],
            $mock_finder_result
        );

        $mock_storage_writer = M::mock(ProjectionWriter::CLASS);
        $this->addExpectationsToStorageWriter($mock_storage_writer, $expectations);

        $mock_event_bus = M::mock(EventBusInterface::CLASS);
        $this->addExpectationsToEventBus($mock_event_bus, $expectations);

        $mock_data_access_service = M::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('getStorageWriter')->once()->andReturn($mock_storage_writer);

        // expectations for loading subject
        $subject = $this->createProjection($subject);
        $mock_storage_reader = M::mock(ProjectionReader::CLASS);
        $mock_storage_reader->shouldReceive('read')->once()->with($subject->getIdentifier())->andReturn($subject);
        $mock_data_access_service->shouldReceive('getStorageReader')
            ->once()
            ->with($subject->getType()->getPrefix() . '::projection.standard::view_store::reader')
            ->andReturn($mock_storage_reader);

        // expectation for loading parent when necessary
        if (!empty($parent)) {
            $parent = $this->createProjection($parent);
            $mock_storage_reader->shouldReceive('read')
                ->once()
                ->with($parent->getIdentifier())
                ->andReturn($parent);
            $mock_data_access_service->shouldReceive('getStorageReader')
                ->once()
                ->with($parent->getType()->getPrefix() . '::projection.standard::view_store::reader')
                ->andReturn($mock_storage_reader);
        }

        // prepare and execute tests
        $projection_updater = new ProjectionUpdater(
            new ArrayConfig([]),
            new NullLogger,
            $mock_data_access_service,
            $mock_query_service_map,
            $this->projection_type_map,
            $this->aggregate_root_type_map,
            $mock_event_bus
        );

        $event = $this->buildEvent($event_state);
        $projection_updater->handleEvent($event);
    }

    // ------------------------ expectation helpers ------------------------

    protected function addProjectionsToMockFinderResult(FinderResultInterface $mock_finder_result, array $projections)
    {
        $mock_finder_result->shouldReceive('hasResults')->times(count($projections))->andReturn(true);
        foreach ($projections as $projection_state) {
            $projection = $this->createProjection($projection_state);
            $mock_finder_result->shouldReceive('getFirstResult')->once()->andReturn($projection);
        }
    }

    protected function addEmdeddedEventsToMockFinder(
        FinderInterface $mock_finder,
        array $embedded_events,
        FinderResultInterface $mock_finder_result
    ) {
        foreach ($embedded_events as $embedded_entity_event) {
            if (isset($embedded_entity_event['data']['referenced_identifier'])
                && strpos($embedded_entity_event['@type'], 'Removed') === false
            ) {
                $mock_finder->shouldReceive('getByIdentifier')
                    ->once()
                    ->with($embedded_entity_event['data']['referenced_identifier'])
                    ->andReturn($mock_finder_result);
            }
        }
        return $mock_finder;
    }

    protected function addExpectationsToStorageWriter(ProjectionWriter $mock_storage_writer, array $expectations)
    {
        foreach ($expectations as $expectation) {
            $mock_storage_writer->shouldReceive('write')
            ->once()
            ->with(M::on(
                function (ProjectionInterface $projection) use ($expectation) {
                    $this->assertEquals($expectation, $projection->toArray());
                    return true;
                }
            ));
        }
    }

    protected function addExpectationsToEventBus(EventBusInterface $mock_event_bus, array $expectations)
    {
        foreach ($expectations as $expectation) {
            $mock_event_bus->shouldReceive('distribute')
                ->once()
                ->with('honeybee.events.infrastructure', M::on(
                    function (ProjectionUpdatedEvent $event) use ($expectation) {
                        $this->assertEquals($expectation['identifier'], $event->getProjectionIdentifier());
                        $this->assertEquals($expectation['@type'] . 'Type', $event->getProjectionType());
                        $this->assertEquals($expectation, $event->getData());
                        return true;
                    }
                ))
                ->andReturnNull();
        }
    }

    // ------------------------------ helpers ------------------------------

    /**
     * @codeCoverageIgnore
     */
    public function provideTestCreatedEvents()
    {
        return $this->loadFixtures('projection_created_test*.php');
    }

    /**
     * @codeCoverageIgnore
     */
    public function provideTestModifiedEvents()
    {
        return $this->loadFixtures('projection_modified_test*.php');
    }

    /**
     * @codeCoverageIgnore
     */
    public function provideTestNodeMovedEvents()
    {
        return $this->loadFixtures('projection_nodemoved_test*.php');
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadFixtures($pattern)
    {
        $tests = [];
        foreach (glob(__DIR__ . '/data/' . $pattern) as $filename) {
            $tests[] = include $filename;
        }
        return $tests;
    }

    protected function buildEvent(array $event_state)
    {
        $event_type_class = $event_state['@type'];
        $embedded_entity_events = new EmbeddedEntityEventList;
        foreach ($event_state['embedded_entity_events'] as $embedded_event_state) {
            $embedded_entity_events->push($this->buildEvent($embedded_event_state));
        }
        $event_state['embedded_entity_events'] = $embedded_entity_events;
        return new $event_type_class($event_state);
    }

    protected function createProjection(array $state)
    {
        return $this->projection_type_map->getByEntityImplementor($state['@type'])->createEntity($state);
    }
}

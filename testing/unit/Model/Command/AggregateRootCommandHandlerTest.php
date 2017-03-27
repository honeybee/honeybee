<?php

namespace Honeybee\Tests\Model\Command;

use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\UnitOfWork\UnitOfWorkInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\NoOpSignal;
use Honeybee\Infrastructure\Workflow\WorkflowServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Command\AggregateRootCommandHandler;
use Honeybee\Model\Command\AggregateRootCommandInterface;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\AggregateRootEventListMap;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Tests\TestCase;
use Mockery;
use Psr\Log\NullLogger;

class AggregateRootCommandHandlerTest extends TestCase
{
    /**
     * @expectedException Honeybee\Common\Error\RuntimeError
     */
    public function testExecuteInvalidCommand()
    {
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_event_bus = Mockery::mock(EventBusInterface::CLASS);
        $mock_workflow_service = Mockery::mock(WorkflowServiceInterface::CLASS);

        $arch = $this->makeHandler($mock_art, $mock_data_access_service, $mock_event_bus, $mock_workflow_service);

        $mock_command = Mockery::mock(CommandInterface::CLASS);
        $arch->execute($mock_command);
    } //@codeCoverageIgnore

    public function testExecuteCreateCommandNoEvents()
    {
        $command_data = ['key' => 'value'];
        $command_metadata = ['meta' => 'meta'];
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('getPrefix')->twice()->withNoArgs()->andReturn('mock_type_prefix');
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_uow = Mockery::mock(UnitOfWorkInterface::CLASS);
        $mock_uow->shouldReceive('create')->once()->withNoArgs()->andReturn($mock_aggregate_root);
        $mock_uow->shouldReceive('commit')->once()->withNoArgs()->andReturn(new AggregateRootEventListMap);
        $uow_prefix = 'mock_type_prefix::domain_event::event_source::unit_of_work';
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('getUnitOfWork')->twice()->with($uow_prefix)->andReturn($mock_uow);
        $mock_event_bus = Mockery::mock(EventBusInterface::CLASS);
        $mock_event_bus->shouldReceive('distribute')->once()->withArgs([
            'honeybee.events.infrastructure',
            Mockery::on(function (NoOpSignal $event) use ($command_data, $command_metadata) {
                $this->assertEquals($command_data, $event->getCommandData());
                $this->assertEquals($command_metadata, $event->getMetadata());
                return true;
            })
        ])->andReturnNull();
        $mock_workflow_service = Mockery::mock(WorkflowServiceInterface::CLASS);

        $arch = $this->makeHandler($mock_art, $mock_data_access_service, $mock_event_bus, $mock_workflow_service);

        $mock_command = Mockery::mock(CreateAggregateRootCommand::CLASS);
        $mock_command->shouldReceive('toArray')->once()->withNoArgs()->andReturn($command_data);
        $mock_command->shouldReceive('getMetadata')->once()->withNoArgs()->andReturn($command_metadata);

        $this->assertNull($arch->execute($mock_command));
    }

    public function testExecuteCreateCommand()
    {
        $command_data = ['key' => 'value'];
        $command_metadata = ['meta' => 'meta'];
        $mock_art = Mockery::mock(AggregateRootTypeInterface::CLASS);
        $mock_art->shouldReceive('getPrefix')->twice()->withNoArgs()->andReturn('mock_type_prefix');
        $mock_aggregate_root = Mockery::mock(AggregateRootInterface::CLASS);
        $mock_uow = Mockery::mock(UnitOfWorkInterface::CLASS);
        $mock_uow->shouldReceive('checkout')->once()->with('mock_identifier')->andReturn($mock_aggregate_root);
        $mock_event1 = Mockery::mock(AggregateRootEventInterface::CLASS);
        $mock_event2 = Mockery::mock(AggregateRootEventInterface::CLASS);
        $aggregate_root_event_list = new AggregateRootEventListMap([
            'event1' => new AggregateRootEventList([$mock_event1]),
            'event2' => new AggregateRootEventList([$mock_event2])
        ]);
        $mock_uow->shouldReceive('commit')->once()->withNoArgs()->andReturn($aggregate_root_event_list);
        $uow_prefix = 'mock_type_prefix::domain_event::event_source::unit_of_work';
        $mock_data_access_service = Mockery::mock(DataAccessServiceInterface::CLASS);
        $mock_data_access_service->shouldReceive('getUnitOfWork')->twice()->with($uow_prefix)->andReturn($mock_uow);
        $mock_event_bus = Mockery::mock(EventBusInterface::CLASS);
        $mock_event_bus->shouldReceive('distribute')->once()->withArgs([
            'honeybee.events.files',
            Mockery::on(function (AggregateRootEventInterface $event) use ($mock_event1) {
                $this->assertEquals($mock_event1, $event);
                return true;
            })
        ])->andReturnNull();
        $mock_event_bus->shouldReceive('distribute')->once()->withArgs([
            'honeybee.events.domain',
            Mockery::on(function (AggregateRootEventInterface $event) use ($mock_event1) {
                $this->assertEquals($mock_event1, $event);
                return true;
            })
        ])->andReturnNull();
        $mock_event_bus->shouldReceive('distribute')->once()->withArgs([
            'honeybee.events.files',
            Mockery::on(function (AggregateRootEventInterface $event) use ($mock_event2) {
                $this->assertEquals($mock_event2, $event);
                return true;
            })
        ])->andReturnNull();
        $mock_event_bus->shouldReceive('distribute')->once()->withArgs([
            'honeybee.events.domain',
            Mockery::on(function (AggregateRootEventInterface $event) use ($mock_event2) {
                $this->assertEquals($mock_event2, $event);
                return true;
            })
        ])->andReturnNull();
        $mock_workflow_service = Mockery::mock(WorkflowServiceInterface::CLASS);

        $arch = $this->makeHandler($mock_art, $mock_data_access_service, $mock_event_bus, $mock_workflow_service);

        $mock_command = Mockery::mock(AggregateRootCommandInterface::CLASS);
        $mock_command->shouldReceive('getAggregateRootIdentifier')->once()->withNoArgs()->andReturn('mock_identifier');
        $this->assertNull($arch->execute($mock_command));
    }

    private function makeHandler($mock_art, $mock_data_access_service, $mock_event_bus, $mock_workflow_service)
    {
        return Mockery::mock(
            AggregateRootCommandHandler::CLASS.'[doExecute]',
            [
                $mock_art,
                $mock_data_access_service,
                $mock_event_bus,
                $mock_workflow_service,
                new NullLogger
            ]
        );
    }
}

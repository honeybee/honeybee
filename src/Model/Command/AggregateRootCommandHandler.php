<?php

namespace Honeybee\Model\Command;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\CommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\NoOpSignal;
use Honeybee\Infrastructure\Workflow\WorkflowServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Psr\Log\LoggerInterface;

abstract class AggregateRootCommandHandler extends CommandHandler
{
    protected $aggregate_root_type;

    protected $event_bus;

    protected $data_access_service;

    protected $workflow_service;

    abstract protected function doExecute(CommandInterface $command, AggregateRootInterface $aggregate_root);

    public function __construct(
        AggregateRootTypeInterface $aggregate_root_type,
        DataAccessServiceInterface $data_access_service,
        EventBusInterface $event_bus,
        LoggerInterface $logger,
        WorkflowServiceInterface $workflow_service
    ) {
        parent::__construct($logger);

        $this->event_bus = $event_bus;
        $this->aggregate_root_type = $aggregate_root_type;
        $this->data_access_service = $data_access_service;
        $this->workflow_service = $workflow_service;
    }

    protected function tryToExecute(CommandInterface $command, $retry_count = 0)
    {
        $aggregate_root = $this->checkoutOrCreateAggregateRoot($command);
        $this->doExecute($command, $aggregate_root);
        $comitted_events = $this->getUnitOfWork()->commit()->filter(function (AggregateRootEventList $event_list) {
            return !$event_list->isEmpty();
        });

        if ($comitted_events->isEmpty()) {
            $this->event_bus->distribute(
                ChannelMap::CHANNEL_INFRA,
                new NoOpSignal([
                    'command_data' => $command->toArray(),
                    'metadata' => $command->getMetadata()
                ])
            );
        } else {
            foreach ($comitted_events as $aggregate_root_id => $event_list) {
                foreach ($event_list as $event) {
                    $this->event_bus->distribute(ChannelMap::CHANNEL_FILES, $event);
                    $this->event_bus->distribute(ChannelMap::CHANNEL_DOMAIN, $event);
                }
            }
        }

        return $comitted_events;
    }

    protected function checkoutOrCreateAggregateRoot(CommandInterface $command)
    {
        if ($command instanceof AggregateRootCommandInterface) {
            $aggregate_root = $this->getUnitOfWork()->checkout($command->getAggregateRootIdentifier());
        } elseif ($command instanceof CreateAggregateRootCommand) {
            $aggregate_root = $this->getUnitOfWork()->create();
        } else {
            throw new RuntimeError(sprintf('Unable to load an aggregate-root for the given command: %s', $command));
        }

        return $aggregate_root;
    }

    protected function getUnitOfWork()
    {
        $uow_key = sprintf('%s::domain_event::event_source::unit_of_work', $this->aggregate_root_type->getPrefix());

        return $this->data_access_service->getUnitOfWork($uow_key);
    }
}

<?php

namespace Honeybee\Model\Command;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Command\CommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\HasEmbeddedEntityEventsInterface;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Psr\Log\LoggerInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;
use Trellis\Runtime\Attribute\HandlesFileListInterface;

abstract class AggregateRootCommandHandler extends CommandHandler
{
    const EVENT_BUS_CHANNEL_PREFIX = 'honeybee.events.';

    protected $aggregate_root_type;

    protected $data_access_service;

    protected $filesystem_service;

    abstract protected function doExecute(CommandInterface $command, AggregateRootInterface $aggregate_root);

    public function __construct(
        AggregateRootTypeInterface $aggregate_root_type,
        DataAccessServiceInterface $data_access_service,
        FilesystemServiceInterface $filesystem_service,
        EventBusInterface $event_bus,
        LoggerInterface $logger
    ) {
        parent::__construct($event_bus, $logger);

        $this->aggregate_root_type = $aggregate_root_type;
        $this->data_access_service = $data_access_service;
        $this->filesystem_service = $filesystem_service;
    }

    protected function tryToExecute(CommandInterface $command, $retry_count = 0)
    {
        $aggregate_root = $this->loadAggregateRoot($command);
        $this->doExecute($command, $aggregate_root);
        // commit pending events and then send them down the event-bus
        $comitted_events = new AggregateRootEventList();
        foreach ($this->getUnitOfWork()->commit() as $aggregate_root_id => $comitted_events_list) {
            foreach ($comitted_events_list as $comitted_event) {
                // move files from tmp- to final-storage
                $this->copyTempFilesToFinalLocation($comitted_event, $aggregate_root->getType());
                $comitted_events->push($comitted_event);
            }
        }

        return $comitted_events;
    }

    protected function loadAggregateRoot(CommandInterface $command)
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

    protected function copyTempFilesToFinalLocation(
        HasEmbeddedEntityEventsInterface $command,
        EntityTypeInterface $entity_type
    ) {
        foreach ($command->getData() as $attr_name => $attr_data) {
            $attribute = $entity_type->getAttribute($attr_name);
            $art = $attribute->getRootType();
            if ($attribute instanceof HandlesFileListInterface) {
                $property_name = $attribute->getFileLocationPropertyName();
                $this->logger->debug(
                    '[{method}] Copying files to "{art}" storage for command "{command}" (attribute "{attr}").',
                    [ 'method' => __METHOD__, 'command' => $command, 'art' => $art->getPrefix(), 'attr' => $attr_name ]
                );
                foreach ($attr_data as $file) {
                    $this->copyTempFileToFinalLocation($file[$property_name], $art);
                }
            } elseif ($attribute instanceof HandlesFileInterface) {
                $this->logger->debug(
                    '[{method}] Copying files to "{art}" storage for command "{command}" (attribute "{attr}").',
                    [ 'method' => __METHOD__, 'command' => $command, 'art' => $art->getPrefix(), 'attr' => $attr_name ]
                );
                $this->copyTempFileToFinalLocation($attr_data[$attribute->getFileLocationPropertyName()], $art);
            }
        }

        // there may be embedded entity commands that have files on their attributes as well => recurse into them
        foreach ($command->getEmbeddedEntityEvents() as $embedded_command) {
            $attr_name = $embedded_command->getParentAttributeName();
            $embedded_entity_type = $embedded_command->getEmbeddedEntityType();
            $embedded_attribute = $entity_type->getAttribute($attr_name);
            $embedded_type = $embedded_attribute->getEmbeddedTypeByPrefix($embedded_entity_type);
            $this->copyTempFilesToFinalLocation($embedded_command, $embedded_type);
        }
    }

    protected function copyTempFileToFinalLocation($location, EntityTypeInterface $art)
    {
        $from_uri = $this->filesystem_service->createTempUri($location, $art); // from temporary storage
        $to_uri = $this->filesystem_service->createUri($location, $art); // to final storage
        $success = false;
        try {
            if (!$this->filesystem_service->has($to_uri)) {
                $success = $this->filesystem_service->copy($from_uri, $to_uri);
            }
        } catch (Exception $copy_error) {
            $this->logger->error(
                '[{method}] File could not be copied from {from_uri} to {to_uri}. Error: {error}',
                [
                    'method' => __METHOD__,
                    'from_uri' => $from_uri,
                    'to_uri' => $to_uri,
                    'error' => $copy_error->getMessage()
                ]
            );
        }

        return $success;
    }

    protected function getUnitOfWork()
    {
        $uow_key = sprintf('%s::domain_event::event_source::unit_of_work', $this->aggregate_root_type->getPrefix());

        return $this->data_access_service->getUnitOfWork($uow_key);
    }

    protected function getEventChannelName($type = 'domain')
    {
        return self::EVENT_BUS_CHANNEL_PREFIX . $type;
    }
}

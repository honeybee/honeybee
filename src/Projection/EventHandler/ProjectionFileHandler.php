<?php

namespace Honeybee\Projection\EventHandler;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Error\FilesystemError;
use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\HasEmbeddedEntityEventsInterface;
use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Psr\Log\LoggerInterface;
use Trellis\Runtime\Attribute\HandlesFileInterface;
use Trellis\Runtime\Attribute\HandlesFileListInterface;

class ProjectionFileHandler extends EventHandler
{
    protected $aggregate_root_type_map;

    protected $filesystem_service;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        AggregateRootTypeMap $aggregate_root_type_map,
        FilesystemServiceInterface $filesystem_service
    ) {
        parent::__construct($config, $logger);

        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->filesystem_service = $filesystem_service;
    }

    protected function onAggregateRootCreated(AggregateRootCreatedEvent $event)
    {
        $ar_type = $this->aggregate_root_type_map->getByClassName($event->getAggregateRootType());
        $this->moveTempFilesToFinalLocation($event, $ar_type);
    }

    protected function onAggregateRootModified(AggregateRootModifiedEvent $event)
    {
        $ar_type = $this->aggregate_root_type_map->getByClassName($event->getAggregateRootType());
        $this->moveTempFilesToFinalLocation($event, $ar_type);
    }

    protected function onWorkflowProceeded(WorkflowProceededEvent $event)
    {
        // @todo do we support file operations upon workflow traversal?
    }

    protected function moveTempFilesToFinalLocation(
        HasEmbeddedEntityEventsInterface $event,
        EntityTypeInterface $entity_type
    ) {
        foreach ($event->getData() as $attr_name => $attr_data) {
            $attribute = $entity_type->getAttribute($attr_name);
            $art = $attribute->getRootType();
            if ($attribute instanceof HandlesFileListInterface) {
                $property_name = $attribute->getFileLocationPropertyName();
                foreach ($attr_data as $file) {
                    $this->moveTempFileToFinalLocation($file[$property_name], $art);
                }
            } elseif ($attribute instanceof HandlesFileInterface) {
                $this->moveTempFileToFinalLocation($attr_data[$attribute->getFileLocationPropertyName()], $art);
            }
        }

        // there may be embedded entity events that have files on their attributes as well => recurse into them
        foreach ($event->getEmbeddedEntityEvents() as $embedded_event) {
            $attr_name = $embedded_event->getParentAttributeName();
            $embedded_entity_type = $embedded_event->getEmbeddedEntityType();
            $embedded_attribute = $entity_type->getAttribute($attr_name);
            $embedded_type = $embedded_attribute->getEmbeddedTypeByPrefix($embedded_entity_type);
            $this->moveTempFilesToFinalLocation($embedded_event, $embedded_type);
        }
    }

    protected function moveTempFileToFinalLocation($location, EntityTypeInterface $art)
    {
        $from_uri = $this->filesystem_service->createTempUri($location, $art); // from temporary storage
        $to_uri = $this->filesystem_service->createUri($location, $art); // to final storage

        try {
            $file_copied = $this->filesystem_service->has($to_uri);
            if (true !== $file_copied) {
                $file_copied = $this->filesystem_service->copy($from_uri, $to_uri);
                if (true !== $file_copied) {
                    throw new FilesystemError(sprintf('File copy failed with result "%s".', $file_copied));
                }
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
            throw new FilesystemError('File could not be copied to final storage.');
        }

        // source file deletion failure is acceptable so the deletion process is done separately.
        // the file must exist at final destination in order to get to this block.
        try {
            $file_deleted = $this->filesystem_service->delete($from_uri);
            if (true !== $file_deleted) {
                throw new FilesystemError(sprintf('File deletion failed with result "%s".', $file_deleted));
            }
        } catch (Exception $deletion_error) {
            // log deletion error and continue
            $this->logger->error(
                '[{method}] File could not be deleted from {from_uri}. Error: {error}',
                [
                    'method' => __METHOD__,
                    'from_uri' => $from_uri,
                    'error' => $deletion_error->getMessage()
                ]
            );
        }

        return true;
    }
}

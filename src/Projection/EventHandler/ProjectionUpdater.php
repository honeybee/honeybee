<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\Event;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\EmbeddedEntityEventInterface;
use Honeybee\Model\Event\EmbeddedEntityEventList;
use Honeybee\Model\Task\CreateAggregateRoot\AggregateRootCreatedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\EmbeddedEntityModifiedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\EmbeddedEntityRemovedEvent;
use Honeybee\Model\Task\MoveAggregateRootNode\AggregateRootNodeMovedEvent;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Projection\ProjectionUpdatedEvent;
use Psr\Log\LoggerInterface;
use Trellis\Runtime\Attribute\AttributeValuePath;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Entity\EntityInterface;
use Trellis\Runtime\Entity\EntityList;
use Trellis\Runtime\Entity\EntityReferenceInterface;
use Trellis\Runtime\ReferencedEntityTypeInterface;

class ProjectionUpdater extends EventHandler
{
    protected $data_access_service;

    protected $projection_type_map;

    protected $aggregate_root_type_map;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        DataAccessServiceInterface $data_access_service,
        ProjectionTypeMap $projection_type_map,
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        parent::__construct($config, $logger);

        $this->data_access_service = $data_access_service;
        $this->projection_type_map = $projection_type_map;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
    }

    public function handleEvent(EventInterface $event)
    {
        $affected_entities = new EntityList;
        $projection = $this->invokeEventHandler($event, 'on');

        if (!$projection) {
            $this->logger->debug(
                '[{method}] Handling {event}.',
                [ 'method' => __METHOD__, 'event' => get_class($event) ]
            );
            switch (true) {
                case $event instanceof AggregateRootCreatedEvent:
                    $projection = $this->onAggregateRootCreated($event);
                    break;
                case $event instanceof WorkflowProceededEvent:
                    $projection = $this->onWorkflowProceeded($event);
                    break;
                case $event instanceof AggregateRootNodeMovedEvent:
                    $projection = $this->onAggregateRootNodeMoved($event);
                    break;
                case $event instanceof AggregateRootModifiedEvent:
                    $projection = $this->onAggregateRootModified($event);
                    break;
                default:
                    // @todo unsupported event type given, log or throw exception?
            }
        }

        if ($projection) {
            $affected_entities->push($projection);
            $this->afterProjectionUpdated($event, $projection);
        }

        return $affected_entities;
    }

    protected function onAggregateRootCreated(AggregateRootCreatedEvent $event)
    {
        $projection_data = $event->getData();
        $projection_data['identifier'] = $event->getAggregateRootIdentifier();
        $projection_data['revision'] = $event->getSeqNumber();
        $projection_data['created_at'] = $event->getDateTime();
        $projection_data['modified_at'] = $event->getDateTime();

        $new_projection = $this->getProjectionType($event)->createEntity($projection_data);
        $this->handleEmbeddedEntityEvents($new_projection, $event->getEmbeddedEntityEvents());
        $this->getStorageWriter($event)->write($new_projection);

        return $new_projection;
    }

    protected function onAggregateRootModified(AggregateRootModifiedEvent $event)
    {
        $updated_data = $this->loadProjection($event)->toArray();

        foreach ($event->getData() as $attribute_name => $new_value) {
            $updated_data[$attribute_name] = $new_value;
        }
        $updated_data['revision'] = $event->getSeqNumber();
        $updated_data['modified_at'] = $event->getDateTime();

        $projection = $this->getProjectionType($event)->createEntity($updated_data);

        $this->mirrorLocalValues($projection, $event);
        $this->handleEmbeddedEntityEvents($projection, $event->getEmbeddedEntityEvents());
        $this->getStorageWriter($event)->write($projection);

        return $projection;
    }

    protected function onWorkflowProceeded(WorkflowProceededEvent $event)
    {
        $updated_data = $this->loadProjection($event)->toArray();
        $updated_data['revision'] = $event->getSeqNumber();
        $updated_data['modified_at'] = $event->getDateTime();
        $updated_data['workflow_state'] = $event->getWorkflowState();
        $workflow_parameters = $event->getWorkflowParameters();
        if ($workflow_parameters !== null) {
            $updated_data['workflow_parameters'] = $workflow_parameters;
        }

        $projection = $this->getProjectionType($event)->createEntity($updated_data);
        $this->getStorageWriter($event)->write($projection);

        return $projection;
    }

    protected function onAggregateRootNodeMoved(AggregateRootNodeMovedEvent $event)
    {
        $parent_projection = $this->loadProjection($event, $event->getParentNodeId());
        $child_projection = $this->loadProjection($event);

        $new_child_path = $parent_projection->getMaterializedPath() . '/' . $event->getParentNodeId();
        $child_data = $child_projection->toArray();
        $child_data['parent_node_id'] = $event->getParentNodeId();
        $child_data['materialized_path'] = $new_child_path;
        $child_data['revision'] = $event->getSeqNumber();
        $this->getStorageWriter($event)->write(
            $this->getProjectionType($event)->createEntity($child_data)
        );

        $child_path_parts = [ $child_projection->getMaterializedPath(), $child_projection->getIdentifier() ];
        $recursive_children_result = $this->getQueryService()->find(
            new Query(
                new CriteriaList,
                new CriteriaList([ new AttributeCriteria('materialized_path', implode('/', $child_path_parts)) ]),
                new CriteriaList,
                0,
                10000
            )
        );
        foreach ($recursive_children_result->getResults() as $affected_ancestor) {
            $ancestor_data = $affected_ancestor->toArray();
            $ancestor_data['materialized_path'] = str_replace(
                $child_projection->getMaterializedPath(),
                $new_child_path,
                $affected_ancestor->getMaterializedPath()
            );
            // @todo introduce a writeMany method to the storageWriter to save requests
            $this->getStorageWriter($event)->write(
                $this->getProjectionType($event)->createEntity($ancestor_data)
            );
        }

        return $child_projection;
    }

    protected function handleEmbeddedEntityEvents(EntityInterface $projection, EmbeddedEntityEventList $events)
    {
        $aggregate_data = [];

        foreach ($events as $event) {
            if ($event instanceof EmbeddedEntityAddedEvent) {
                $this->onEmbeddedEntityAdded($projection, $event);
            } else if ($event instanceof EmbeddedEntityModifiedEvent) {
                $this->onEmbeddedEntityModified($projection, $event);
            } else if ($event instanceof EmbeddedEntityRemovedEvent) {
                $this->onEmbeddedEntityRemoved($projection, $event);
            } else {
                throw new RuntimeError(
                    sprintf(
                        'Unsupported domain event-type given. Supported default event-types are: %s.',
                        implode(
                            ', ',
                            [
                                EmbeddedEntityAddedEvent::CLASS,
                                EmbeddedEntityModifiedEvent::CLASS,
                                EmbeddedEntityRemovedEvent::CLASS
                            ]
                        )
                    )
                );
            }
        }

        return $aggregate_data;
    }

    protected function onEmbeddedEntityAdded(EntityInterface $projection, EmbeddedEntityAddedEvent $event)
    {
        $embedded_projection_attr = $projection->getType()->getAttribute($event->getParentAttributeName());
        $embedded_projection_type = $this->getEmbeddedEntityTypeFor($projection, $event);
        $embedded_projection = $embedded_projection_type->createEntity($event->getData(), $projection);
        $projection_list = $projection->getValue($embedded_projection_attr->getName());

        if ($embedded_projection_type instanceof ReferencedEntityTypeInterface) {
            $embedded_projection = $this->mirrorForeignValues($embedded_projection);
        }

        $projection_list->insertAt($event->getPosition(), $embedded_projection);

        $this->handleEmbeddedEntityEvents($embedded_projection, $event->getEmbeddedEntityEvents());
    }

    protected function onEmbeddedEntityModified(EntityInterface $projection, EmbeddedEntityModifiedEvent $event)
    {
        $embedded_projection_attr = $projection->getType()->getAttribute($event->getParentAttributeName());
        $embedded_projection_type = $this->getEmbeddedEntityTypeFor($projection, $event);

        $projection_list = $projection->getValue($embedded_projection_attr->getName());
        $projection_to_modify = null;
        foreach ($projection_list as $projection) {
            if ($projection->getIdentifier() === $event->getEmbeddedEntityIdentifier()) {
                $projection_to_modify = $projection;
            }
        }

        if ($projection_to_modify) {
            $projection_list->removeItem($projection_to_modify);
            $projection_to_modify = $embedded_projection_type->createEntity(
                array_merge($projection_to_modify->toArray(), $event->getData()),
                $projection
            );

            if ($embedded_projection_type instanceof ReferencedEntityTypeInterface) {
                $projection_to_modify = $this->mirrorForeignValues($projection_to_modify);
            }

            $projection_list->insertAt($event->getPosition(), $projection_to_modify);
            $this->handleEmbeddedEntityEvents($projection_to_modify, $event->getEmbeddedEntityEvents());
        }
    }

    protected function onEmbeddedEntityRemoved(EntityInterface $projection, EmbeddedEntityRemovedEvent $event)
    {
        $projection_list = $projection->getValue($event->getParentAttributeName());
        $projection_to_remove = null;

        foreach ($projection_list as $embedded_projection) {
            if ($embedded_projection->getIdentifier() === $event->getEmbeddedEntityIdentifier()) {
                $projection_to_remove = $embedded_projection;
            }
        }

        if ($projection_to_remove) {
            $projection_list->removeItem($projection_to_remove);
        }
    }

    protected function afterProjectionUpdated(AggregateRootEventInterface $source_event, EntityInterface $projection)
    {
        $this->invokeEventHandler($source_event, 'after', [ $projection ]);

        $updated_event = new ProjectionUpdatedEvent(
            [
                'uuid' => $projection->getUuid(),
                'source_event_data' => $source_event->toArray(),
                'projection_type' => $projection->getType()->getPrefix(),
                'projection_data' => $projection->toArray()
            ]
        );
        // @todo post ProjectionUpdatedEvent to the event-bus ('honeybee.projection_events' channel)
        // this requires a dep to the event-bus, which will cause a cycle within the di-container
        // in order to get it to work, we could create a LazyEventSubscription as done within the CommandBus
    }

    protected function mirrorForeignValues(EntityInterface $projection)
    {
        $mirrored_attributes_map = $projection->getType()->getAttributes()->filter(
            function ($attribute) {
                return ((bool)$attribute->getOption('mirrored', false)) === true;
            }
        );
        if ($mirrored_attributes_map->isEmpty()) {
            return $projection;
        }
        $referenced_type = $this->projection_type_map->getByClassName(
            $projection->getType()->getReferencedTypeClass()
        );
        $referenced_identifier = $projection->getReferencedIdentifier();

        if ($referenced_identifier === $projection->getRoot()->getIdentifier()) {
            $referenced_projection = $projection->getRoot(); // self reference, no need to load
        } else {
            $search_result = $this->getFinder($referenced_type)->getByIdentifier($referenced_identifier);
            if (!$search_result->hasResults()) {
                // zombie reference, shouldn't happen.
                $this->logger->debug('Unable to resolve referenced projection: ' . $referenced_identifier);
                return $projection;
            }
            $referenced_projection = $search_result->getFirstResult();
        }

        $mirrored_values = [];
        foreach ($mirrored_attributes_map->getKeys() as $mirrored_attribute_name) {
            $mirrored_value = $referenced_projection->getValue($mirrored_attribute_name);
            $mirrored_values[$mirrored_attribute_name] = $mirrored_value;
        }

        return $projection->getType()->createEntity(
            array_merge($projection->toNative(), $mirrored_values),
            $projection->getParent()
        );
    }

    protected function mirrorLocalValues(
        ProjectionInterface $projection,
        AggregateRootEventInterface $event
    ) {
        $affected_attributes = array_keys($event->getData());
        foreach ($event->getEmbeddedEntityEvents() as $embedded_event) {
            $affected_attributes[] = $embedded_event->getParentAttributeName();
        }
        $attributes_to_update = [];

        $source_ar_type = $this->aggregate_root_type_map->getByClassName($event->getAggregateRootType());
        if ($source_ar_type->getPrefix() !== $projection->getType()->getPrefix()) {
            return;
        }

        $source_classname = get_class($projection->getType());
        foreach ($projection->getType()->getReferenceAttributes() as $attribute_path => $ref_attribute) {
            $mirror_attributes = [];
            foreach ($ref_attribute->getEmbeddedEntityTypeMap() as $reference_embed) {
                $referenced_classname = ltrim($reference_embed->getReferencedTypeClass(), '\\');
                if ($referenced_classname === $source_classname) {
                    $attributes_to_mirror = $reference_embed->getAttributes()->filter(
                        function($attribute) use ($affected_attributes) {
                            return in_array($attribute->getName(), $affected_attributes)
                                && (bool)$attribute->getOption('mirrored', false);
                        }
                    );
                    if (!$attributes_to_mirror->isEmpty()) {
                        $mirror_attributes[$reference_embed->getPrefix()] = $attributes_to_mirror->getKeys();
                    }
                }
            }
            if (!empty($mirror_attributes)) {
                $attributes_to_update[$attribute_path] = $mirror_attributes;
            }
        }

        foreach ($attributes_to_update as $attribute_path => $mirror_attributes) {
            $reference_embeds = AttributeValuePath::getAttributeValueByPath($projection, $attribute_path);
            foreach ($reference_embeds as $pos => $reference_embed) {
                if ($reference_embed->getReferencedIdentifier() === $event->getAggregateRootIdentifier()) {
                $reference_embeds->removeItem($reference_embed);
                    $reference_embeds->insertAt(
                        $pos,
                        $reference_embed->getType()->createEntity(
                            array_merge($reference_embed->toNative(), $event->getData()),
                            $reference_embed->getParent()
                        )
                    );
                }
            }
        }
    }

    protected function loadProjection(AggregateRootEventInterface $event, $identifier = null)
    {
        return $this->getStorageReader($event)->read($identifier ?: $event->getAggregateRootIdentifier());
    }

    protected function getEmbeddedEntityTypeFor(EntityInterface $projection, EmbeddedEntityEventInterface $event)
    {
        $embed_attribute = $projection->getType()->getAttribute($event->getParentAttributeName());

        return $embed_attribute->getEmbeddedTypeByPrefix($event->getEmbeddedEntityType());
    }

    protected function getProjectionType(AggregateRootEventInterface $event)
    {
        $ar_type = $this->aggregate_root_type_map->getByClassName($event->getAggregateRootType());

        if (!$this->projection_type_map->hasKey($ar_type->getPrefix())) {
            throw new RuntimeError('Unable to resolve projection type for prefix: ' . $ar_type->getPrefix());
        }

        return $this->projection_type_map->getItem($ar_type->getPrefix());
    }

    protected function getStorageWriter(AggregateRootEventInterface $event)
    {
        $projection_type = $this->getProjectionType($event);
        return $this->getDataAccessComponent($this->getProjectionType($event), 'writer');
    }

    protected function getStorageReader(AggregateRootEventInterface $event)
    {
        return $this->getDataAccessComponent($this->getProjectionType($event), 'reader');
    }

    protected function getFinder(EntityTypeInterface $entity_type)
    {
        return $this->getDataAccessComponent($entity_type, 'finder');
    }

    protected function getDataAccessComponent(ProjectionTypeInterface $projection_type, $component = 'reader')
    {
        $default_component_name = sprintf(
            '%s::projection.standard::view_store::%s',
            $projection_type->getPrefix(),
            $component
        );
        $custom_component_option = $projection_type->getPrefix() . '.' . $component;

        switch ($component) {
            case 'finder':
                return $this->data_access_service->getFinder(
                    $this->config->get($custom_component_option, $default_component_name)
                );
                break;
            case 'reader':
                return $this->data_access_service->getStorageReader(
                    $this->config->get($custom_component_option, $default_component_name)
                );
                break;
            case 'writer':
                return $this->data_access_service->getStorageWriter(
                    $this->config->get($custom_component_option, $default_component_name)
                );
                break;
        }

        throw new RuntimeError('Invalid dbal component name given: ' . $component);
    }
}

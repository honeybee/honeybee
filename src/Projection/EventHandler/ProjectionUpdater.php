<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\EntityTypeInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
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
use Honeybee\Projection\Event\ProjectionCreatedEvent;
use Honeybee\Projection\Event\ProjectionUpdatedEvent;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Projection\ProjectionTypeMap;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Trellis\Runtime\Attribute\AttributeInterface;
use Trellis\Runtime\Entity\EntityInterface;
use Trellis\Runtime\Entity\EntityReferenceInterface;
use Trellis\Runtime\ReferencedEntityTypeInterface;

class ProjectionUpdater extends EventHandler
{
    protected $data_access_service;

    protected $projection_type_map;

    protected $aggregate_root_type_map;

    protected $event_bus;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        DataAccessServiceInterface $data_access_service,
        ProjectionTypeMap $projection_type_map,
        AggregateRootTypeMap $aggregate_root_type_map,
        EventBusInterface $event_bus
    ) {
        parent::__construct($config, $logger);

        $this->data_access_service = $data_access_service;
        $this->projection_type_map = $projection_type_map;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
        $this->event_bus = $event_bus;
    }

    public function handleEvent(EventInterface $event)
    {
        $affected_projections = $this->invokeEventHandler($event, 'on');

        // store updates and distribute projection update events
        $projection_type = $this->getProjectionType($event);
        $this->getStorageWriter($projection_type)->writeMany($affected_projections);

        foreach ($affected_projections as $affected_projection) {
            $projection_event_state = [
                'uuid' => Uuid::uuid4()->toString(),
                'projection_identifier' => $affected_projection->getIdentifier(),
                'projection_type' => $affected_projection->getType()->getVariantPrefix(),
                'data' => $affected_projection->toArray()
            ];

            $projection_event = $event instanceof AggregateRootCreatedEvent
                ? new ProjectionCreatedEvent($projection_event_state)
                : new ProjectionUpdatedEvent($projection_event_state);

            $this->event_bus->distribute(ChannelMap::CHANNEL_INFRA, $projection_event);
        }

        // call any dependent handlers
        foreach ($affected_projections as $affected_projection) {
            $this->invokeEventHandler($event, 'after', [ $affected_projection ]);
        }

        return $affected_projections->toList()->getFirst();
    }

    protected function onAggregateRootCreated(AggregateRootCreatedEvent $event)
    {
        $projection_data = $event->getData();
        $projection_data['identifier'] = $event->getAggregateRootIdentifier();
        $projection_data['revision'] = $event->getSeqNumber();
        $projection_data['created_at'] = $event->getDateTime();
        $projection_data['modified_at'] = $event->getDateTime();
        $projection_data['metadata'] = $event->getMetaData();

        $projection_type = $this->getProjectionType($event);

        if ($projection_type->isHierarchical()) {
            $parent_projection = null;
            if (isset($projection_data['parent_node_id'])) {
                $parent_projection = $this->loadProjection($projection_type, $projection_data['parent_node_id']);
            }
            $projection_data['materialized_path'] = $this->calculateMaterializedPath($parent_projection);
        }

        $new_projection = $projection_type->createEntity($projection_data);
        $this->handleEmbeddedEntityEvents($new_projection, $event->getEmbeddedEntityEvents());

        return new ProjectionMap([ $new_projection ]);
    }

    protected function onAggregateRootModified(AggregateRootModifiedEvent $event)
    {
        $projection_type = $this->getProjectionType($event);
        $updated_data = $this->loadProjection($projection_type, $event->getAggregateRootIdentifier())->toArray();

        foreach ($event->getData() as $attribute_name => $new_value) {
            $updated_data[$attribute_name] = $new_value;
        }
        $updated_data['revision'] = $event->getSeqNumber();
        $updated_data['modified_at'] = $event->getDateTime();
        $updated_data['metadata'] = array_merge($updated_data['metadata'], $event->getMetaData());

        $projection = $projection_type->createEntity($updated_data);
        $this->handleEmbeddedEntityEvents($projection, $event->getEmbeddedEntityEvents());

        return new ProjectionMap([ $projection ]);
    }

    protected function onWorkflowProceeded(WorkflowProceededEvent $event)
    {
        $projection_type = $this->getProjectionType($event);
        $projection = $this->loadProjection($projection_type, $event->getAggregateRootIdentifier());

        $updated_data = $projection->toArray();
        $updated_data['revision'] = $event->getSeqNumber();
        $updated_data['modified_at'] = $event->getDateTime();
        $updated_data['metadata'] = array_merge($updated_data['metadata'], $event->getMetaData());
        $updated_data['workflow_state'] = $event->getWorkflowState();
        $workflow_parameters = $event->getWorkflowParameters();
        if ($workflow_parameters !== null) {
            $updated_data['workflow_parameters'] = $workflow_parameters;
        }

        $projection = $projection_type->createEntity($updated_data);

        return new ProjectionMap([ $projection ]);
    }

    protected function onAggregateRootNodeMoved(AggregateRootNodeMovedEvent $event)
    {
        $projection_type = $this->getProjectionType($event);
        $projection = $this->loadProjection($projection_type, $event->getAggregateRootIdentifier());

        $parent_projection = null;
        if ($parent_identifier = $event->getParentNodeId()) {
            $parent_projection = $this->loadProjection($projection_type, $parent_identifier);
        }

        $updated_data = $projection->toArray();
        $updated_data['revision'] = $event->getSeqNumber();
        $updated_data['modified_at'] = $event->getDateTime();
        $updated_data['metadata'] = array_merge($updated_data['metadata'], $event->getMetaData());
        $updated_data['parent_node_id'] = $parent_identifier;
        $updated_data['materialized_path'] = $this->calculateMaterializedPath($parent_projection);

        $updated_projections = [ $projection_type->createEntity($updated_data) ];
        $updated_projections = array_merge(
            $updated_projections,
            $this->updateChildNodesAfterMovingParent($updated_projections[0])
        );

        return new ProjectionMap($updated_projections);
    }

    protected function updateChildNodesAfterMovingParent(EntityInterface $parent)
    {
        // find all existing children of the moved parent node
        $projection_type = $parent->getType();
        $parent_identifier = $parent->getIdentifier();
        $path = $this->calculateMaterializedPath($parent);

        $affected_relatives = [];
        $this->getQueryService($projection_type)->scroll(
            new CriteriaQuery(
                new CriteriaList,
                new CriteriaList(
                    [ new AttributeCriteria('materialized_path', new Equals($parent_identifier)) ]
                ),
                new CriteriaList,
                0,
                $this->config->get('batch_size', 1000)
            ),
            function (
                ProjectionInterface $projection
            ) use (
                &$affected_relatives,
                $parent_identifier,
                $projection_type,
                $path
            ) {
                // @note if there are many affected relatives this could consume memory
                $child_data = $projection->toArray();
                $pattern = '#.*' . $parent_identifier . '#';
                $child_data['materialized_path'] = preg_replace($pattern, $path, $projection->getMaterializedPath());
                $affected_relatives[] = $projection_type->createEntity($child_data);
            }
        );

        return $affected_relatives;
    }

    protected function calculateMaterializedPath(ProjectionInterface $parent = null)
    {
        $path_parts = [];
        if ($parent) {
            $parent_path = $parent->getMaterializedPath();
            if (!empty($parent_path)) {
                $path_parts = explode('/', $parent_path);
            }
            $path_parts[] = $parent->getIdentifier();
        }

        return implode('/', $path_parts);
    }

    protected function handleEmbeddedEntityEvents(EntityInterface $projection, EmbeddedEntityEventList $events)
    {
        foreach ($events as $event) {
            if ($event instanceof EmbeddedEntityAddedEvent) {
                $this->onEmbeddedEntityAdded($projection, $event);
            } elseif ($event instanceof EmbeddedEntityModifiedEvent) {
                $this->onEmbeddedEntityModified($projection, $event);
            } elseif ($event instanceof EmbeddedEntityRemovedEvent) {
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
    }

    protected function onEmbeddedEntityAdded(EntityInterface $projection, EmbeddedEntityAddedEvent $event)
    {
        if(!$projection->getType()->hasAttribute($event->getParentAttributeName())){
            return false;
        }

        $embedded_projection_attr = $projection->getType()->getAttribute($event->getParentAttributeName());
        $embedded_projection_type = $this->getEmbeddedEntityType($projection, $event);
        $embedded_projection = $embedded_projection_type->createEntity($event->getData(), $projection);
        $projection_list = $projection->getValue($embedded_projection_attr->getName());
        if ($embedded_projection_type instanceof ReferencedEntityTypeInterface) {
            $embedded_projection = $this->mirrorReferencedProjection($embedded_projection);
        }

        $projection_list->insertAt($event->getPosition(), $embedded_projection);

        $this->handleEmbeddedEntityEvents($embedded_projection, $event->getEmbeddedEntityEvents());
    }

    protected function onEmbeddedEntityModified(EntityInterface $projection, EmbeddedEntityModifiedEvent $event)
    {
        $embedded_projection_attr = $projection->getType()->getAttribute($event->getParentAttributeName());
        $embedded_projection_type = $this->getEmbeddedEntityType($projection, $event);

        $embedded_projections = $projection->getValue($embedded_projection_attr->getName());
        $projection_to_modify = null;
        foreach ($embedded_projections as $embedded_projection) {
            if ($embedded_projection->getIdentifier() === $event->getEmbeddedEntityIdentifier()) {
                $projection_to_modify = $embedded_projection;
            }
        }

        if ($projection_to_modify) {
            $embedded_projections->removeItem($projection_to_modify);
            $projection_to_modify = $embedded_projection_type->createEntity(
                array_merge($projection_to_modify->toArray(), $event->getData()),
                $projection
            );

            if ($embedded_projection_type instanceof ReferencedEntityTypeInterface) {
                $projection_to_modify = $this->mirrorReferencedProjection($projection_to_modify);
            }

            $embedded_projections->insertAt($event->getPosition(), $projection_to_modify);
            $this->handleEmbeddedEntityEvents($projection_to_modify, $event->getEmbeddedEntityEvents());
        }
    }

    protected function onEmbeddedEntityRemoved(EntityInterface $projection, EmbeddedEntityRemovedEvent $event)
    {
        if(!$projection->getType()->hasAttribute($event->getParentAttributeName())){
            return false;
        }

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

    /**
     * Evaluate and updated mirrored values from a loaded referenced projection
     */
    protected function mirrorReferencedProjection(EntityReferenceInterface $projection)
    {
        $projection_type = $projection->getType();
        $mirrored_attribute_map = $projection_type->getAttributes()->filter(
            function (AttributeInterface $attribute) {
                return (bool)$attribute->getOption('mirrored', false) === true;
            }
        );

        // Don't need to load a referenced entity if the mirrored attribute map is empty
        if ($mirrored_attribute_map->isEmpty()) {
            return $projection;
        }

        // Load the referenced projection to mirror values from
        $referenced_type = $this->projection_type_map->getByClassName(
            $projection_type->getReferencedTypeClass()
        );
        $referenced_identifier = $projection->getReferencedIdentifier();
        if (empty($referenced_identifier)) {
            $this->logger->error('[Zombie Alarm] RefId EMPTY: '.json_encode($projection->getRoot()->toArray()));
            return $projection;
        }

        if ($referenced_identifier === $projection->getRoot()->getIdentifier()) {
            $referenced_projection = $projection->getRoot(); // self reference, no need to load
        } else {
            $referenced_projection = $this->loadReferencedProjection($referenced_type, $referenced_identifier);
            if (!$referenced_projection) {
                $this->logger->debug('[Zombie Alarm] Unable to load referenced projection: ' . $referenced_identifier);
                return $projection;
            }
        }

        // Add default attribute values
        $mirrored_values['@type'] = $projection_type->getPrefix();
        $mirrored_values['identifier'] = $projection->getIdentifier();
        $mirrored_values['referenced_identifier'] = $projection->getReferencedIdentifier();
        $mirrored_values = array_merge(
            $projection->createMirrorFrom($referenced_projection)->toArray(),
            $mirrored_values
        );

        return $projection_type->createEntity($mirrored_values, $projection->getParent());
    }

    protected function loadProjection(ProjectionTypeInterface $projection_type, $identifier)
    {
        return $this->getStorageReader($projection_type)->read($identifier);
    }

    protected function loadReferencedProjection(EntityTypeInterface $referenced_type, $identifier)
    {
        $search_result = $this->getFinder($referenced_type)->getByIdentifier($identifier);
        if (!$search_result->hasResults()) {
            return null;
        }
        return $search_result->getFirstResult();
    }

    protected function getEmbeddedEntityType(EntityInterface $projection, EmbeddedEntityEventInterface $event)
    {
        $embed_attribute = $projection->getType()->getAttribute($event->getParentAttributeName());
        return $embed_attribute->getEmbeddedTypeByPrefix($event->getEmbeddedEntityType());
    }

    protected function getProjectionType(AggregateRootEventInterface $event)
    {
        return $this->projection_type_map->getByAggregateRootType(
            $this->aggregate_root_type_map->getItem($event->getAggregateRootType())
        );
    }

    protected function getQueryService(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'query_service');
    }

    protected function getStorageWriter(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'writer');
    }

    protected function getStorageReader(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'reader');
    }

    protected function getFinder(ProjectionTypeInterface $projection_type)
    {
        return $this->getDataAccessComponent($projection_type, 'finder');
    }

    protected function getDataAccessComponent(ProjectionTypeInterface $projection_type, $component)
    {
        $default_component_name = sprintf('%s::view_store::%s', $projection_type->getVariantPrefix(), $component);
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
            case 'query_service':
                return $this->data_access_service->getQueryService(
                    $this->config->get($custom_component_option, $default_component_name)
                );
                break;
        }

        throw new RuntimeError('Invalid data access component name given: ' . $component);
    }
}

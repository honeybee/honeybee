<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\EntityInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Projection\Entity;
use Honeybee\Projection\EntityType;
use Honeybee\Projection\Event\ProjectionUpdatedEvent;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\ProjectionMap;
use Honeybee\Projection\ProjectionTypeInterface;
use Honeybee\Projection\ProjectionTypeMap;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Trellis\Entity\ReferenceInterface;
use Trellis\EntityType\Attribute\AttributeInterface;
use Trellis\EntityType\Attribute\EntityList\EntityListAttribute;
use Trellis\EntityType\Attribute\ReferenceList\ReferenceListAttribute;
use Trellis\EntityType\TypeReferenceInterface;

class RelationProjectionUpdater extends EventHandler
{
    /**
     * @var StorageWriterMap $storage_writer_map
     */
    protected $storage_writer_map;

    /**
     * @var QueryServiceMap $query_service_map
     */
    protected $query_service_map;

    /**
     * @var ProjectionTypeMap $projection_type_map
     */
    protected $projection_type_map;

    /**
     * @var EventBusInterface $event_bus
     */
    protected $event_bus;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param StorageWriterMap $storage_writer_map
     * @param QueryServiceMap $query_service_map
     * @param ProjectionTypeMap $projection_type_map
     * @param EventBusInterface $event_bus
     */
    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        StorageWriterMap $storage_writer_map,
        QueryServiceMap $query_service_map,
        ProjectionTypeMap $projection_type_map,
        EventBusInterface $event_bus
    ) {
        parent::__construct($config, $logger);

        $this->storage_writer_map = $storage_writer_map;
        $this->query_service_map = $query_service_map;
        $this->projection_type_map = $projection_type_map;
        $this->event_bus = $event_bus;
    }

    /**
     * @param EventInterface $event
     *
     * @return bool|mixed
     */
    public function handleEvent(EventInterface $event)
    {
        return $this->invokeEventHandler($event, 'on');
    }

    /**
     * @param ProjectionUpdatedEvent $event
     *
     * @return ProjectionMap
     */
    protected function onProjectionUpdated(ProjectionUpdatedEvent $event)
    {
        $affected_relatives = $this->loadAffectedRelativesFromProjectionEvent($event);

        // reconstruct complete projection from event data
        $source_projection_type = $this->projection_type_map->getItem($event->getProjectionType());
        $source_projection = $source_projection_type->createEntity($event->getData());

        $updated_relatives = $this->updateAffectedRelatives($affected_relatives, $source_projection);

        $this->storeUpdatedProjections($affected_relatives, $updated_relatives);

        return $updated_relatives;
    }

    /**
     * @param ProjectionMap $affected_relatives
     * @param ProjectionInterface $source_projection
     *
     * @return ProjectionMap
     */
    protected function updateAffectedRelatives(
        ProjectionMap $affected_relatives,
        ProjectionInterface $source_projection
    ) {
        $referenced_identifier = $source_projection->getIdentifier();
        $updated_relatives = [];
        foreach ($affected_relatives as $affected_relative) {
            // collate the paths and matching entity list attributes from the affected projection
            $updated_state = $affected_relative->toArray();
            $affected_relative_type = $affected_relative->getType();
            $affected_entities = $affected_relative->collateChildren(
                function (EntityInterface $embedded_entity) use ($referenced_identifier) {
                    return $embedded_entity instanceof ReferenceInterface
                        && $embedded_entity->getReferencedIdentifier() === $referenced_identifier;
                }
            );

            // reconstruct related projection state adding the updated mirrored values
            /* @var Entity $affected_entity
             * @var EntityType $affected_entity_type */
            foreach ($affected_entities as $affected_entity_value_path => $affected_entity) {
                $affected_entity_type = $affected_entity->getEntityType();
                $affected_entity_prefix = $affected_entity_type->getPrefix();
                $mirrored_values = $affected_entity_type
                    ->createMirroredEntity($source_projection, $affected_entity)
                        ->toArray();
                // @todo if the current affected entity type has no mirrored attributes we can cache the
                // mirrored values and improve performance by skipping additional unecessary recursion
                $mirrored_values['@type'] = $affected_entity_prefix;
                $mirrored_values['identifier'] = $affected_entity->getIdentifier();
                $mirrored_values['referenced_identifier'] = $affected_entity->getReferencedIdentifier();
                // insert the mirrored values in the correct position in our updated state
                preg_match_all(
                    '#(?<parent>[a-z]+)\.[a-z]+\[(?<position>\d+)\]\.?#',
                    $affected_entity_value_path,
                    $value_path_parts,
                    PREG_SET_ORDER
                );
                $nested_value = &$updated_state;
                foreach ($value_path_parts as $value_path_part) {
                    $nested_value = &$nested_value[$value_path_part['parent']][$value_path_part['position']];
                }
                $nested_value = $mirrored_values;
            }

            // create the new projection from the updated state
            $updated_relative = $affected_relative_type->createEntity($updated_state);
            $updated_relatives[] = $updated_relative;
        }

        return new ProjectionMap($updated_relatives);
    }

    /**
     * @param ProjectionMap $affected_relatives
     * @param ProjectionMap $updated_relatives
     *
     * @todo investigate possible edge cases where circular dependencies cause endless updates
     */
    protected function storeUpdatedProjections(ProjectionMap $affected_relatives, ProjectionMap $updated_relatives)
    {
        $modified_relatives = $updated_relatives->filter(
            function (ProjectionInterface $projection) use ($affected_relatives) {
                $identifier = $projection->getIdentifier();
                return $projection->toArray() !== $affected_relatives->getItem($identifier)->toArray();
            }
        );

        // store updates and distribute events
        $this->getStorageWriter()->writeMany($modified_relatives);
        foreach ($modified_relatives as $identifier => $modified_relative) {
            $update_event = new ProjectionUpdatedEvent(
                [
                    'uuid' => Uuid::uuid4()->toString(),
                    'projection_identifier' => $identifier,
                    'projection_type' => $modified_relative->getType()->getPrefix(),
                    'data' => $modified_relative->toArray()
                ]
            );
            $this->event_bus->distribute(ChannelMap::CHANNEL_INFRA, $update_event);
        }
    }

    /**
     * @param ProjectionUpdatedEvent $event
     *
     * @return ProjectionMap
     */
    protected function loadAffectedRelativesFromProjectionEvent(ProjectionUpdatedEvent $event)
    {
        // we don't know what exactly has changed in the source projection so first we filter out
        // reference attributes not referencing the type of the updated projection
        $referenced_attributes_map = $this->getReferencingtAttributess(
            $this->getRelativeProjectionType(),
            get_class($this->projection_type_map->getItem($event->getProjectionType()))
        );

        // build filter criteria to load projections where references may need to be updated
        $reference_filter_list = new CriteriaList([], CriteriaList::OP_OR);
        foreach ($referenced_attributes_map as $ref_attribute) {
            $reference_filter_list->push(
                new AttributeCriteria(
                    $this->buildFieldFilterSpec($ref_attribute),
                    new Equals($event->getProjectionIdentifier())
                )
            );
        }

        return $this->buildQuery($event->getProjectionIdentifier(), $reference_filter_list);
    }

    /**
     * finalize query and get results from the query service
     *
     * @param $identifier
     * @param CriteriaList $reference_filter_list
     *
     * @return ProjectionMap
     */
    protected function buildQuery($identifier, CriteriaList $reference_filter_list)
    {
        $affected_relatives = [];
        if (!empty($reference_filter_list)) {
            // prevent circular self reference loading
            $filter_criteria_list = new CriteriaList;
            $filter_criteria_list->push(
                new AttributeCriteria('identifier', new Equals($identifier, true))
            );
            $filter_criteria_list->push($reference_filter_list);

            // @todo scan and scroll support
            $query_result = $this->getQueryService()->find(
                new Query(
                    new CriteriaList,
                    $filter_criteria_list,
                    new CriteriaList,
                    0,
                    10000
                )
            );
            foreach ($query_result->getResults() as $affected_relative) {
                $affected_relatives[] = $affected_relative;
            }
        }

        return new ProjectionMap($affected_relatives);
    }

    /**
     * @param EntityListAttribute $embed_attribute
     *
     * @return string
     */
    protected function buildFieldFilterSpec(EntityListAttribute $embed_attribute)
    {
        $filter_parts = [];
        $parent_attribute = $embed_attribute->getParent();
        while ($parent_attribute) {
            $filter_parts[] = $parent_attribute->getName();
            $parent_attribute = $parent_attribute->getParent();
        }
        $filter_parts[] = $embed_attribute->getName();
        $filter_parts[] = 'referenced_identifier';

        return implode('.', $filter_parts);
    }

    /**
     * @return ProjectionTypeInterface
     */
    protected function getRelativeProjectionType()
    {
        $projection_type = $this->config->get('projection_type');

        return $this->projection_type_map->getItem($projection_type);
    }

    /**
     * @return QueryServiceInterface
     */
    protected function getQueryService()
    {
        $query_service_default = sprintf('%s::query_service', $this->getRelativeProjectionType()->getPrefix());
        $query_service_key = $this->config->get('query_service', $query_service_default);

        return $this->query_service_map->getItem($query_service_key);
    }

    /**
     * @return StorageWriterInterface
     */
    protected function getStorageWriter()
    {
        $storage_writer_default = sprintf(
            '%s::projection.standard::view_store::writer',
            $this->getRelativeProjectionType()->getPrefix()
        );
        $storage_writer_key = $this->config->get('storage_writer', $storage_writer_default);

        return $this->storage_writer_map->getItem($storage_writer_key);
    }

    /**
     * @param ProjectionTypeInterface $projection_type
     * @param $referenced_class
     *
     * @return \Trellis\EntityType\Attribute\AttributeMap
     */
    protected function getReferencingtAttributess(ProjectionTypeInterface $projection_type, $referenced_class)
    {
        return $projection_type->getAttributes()
            ->collate(
                function (AttributeInterface $attribute) use ($referenced_class) {
                    if (!$attribute instanceof ReferenceListAttribute) {
                        return false;
                    }
                    /* @var ReferenceListAttribute $attribute
                     * @var TypeReferenceInterface $ref_embed */
                    foreach ($attribute->getEntityTypeMap() as $ref_embed) {
                        $ref_child_class = ltrim($ref_embed->getReferencedTypeClass(), '\\');
                        if ($ref_child_class === $referenced_class) {
                            return true;
                        }
                    }
                    return false;
                }
            );
    }
}

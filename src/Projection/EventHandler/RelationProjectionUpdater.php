<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
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

class RelationProjectionUpdater extends EventHandler
{
    protected $storage_writer_map;

    protected $query_service_map;

    protected $projection_type_map;

    protected $aggregate_root_type_map;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        StorageWriterMap $storage_writer_map,
        QueryServiceMap $query_service_map,
        ProjectionTypeMap $projection_type_map,
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        parent::__construct($config, $logger);

        $this->storage_writer_map = $storage_writer_map;
        $this->query_service_map = $query_service_map;
        $this->projection_type_map = $projection_type_map;
        $this->aggregate_root_type_map = $aggregate_root_type_map;
    }

    public function handleEvent(EventInterface $event)
    {
        return $this->updateAffectedRelatives($event);
    }

    protected function updateAffectedRelatives(AggregateRootEventInterface $event)
    {
        $affected_ar_type = $this->aggregate_root_type_map->getByClassName($event->getAggregateRootType());
        $foreign_projection_type = $this->projection_type_map->getItem($affected_ar_type->getPrefix());
        $foreign_projection_type_impl = get_class($foreign_projection_type);

        $affected_attributes = array_keys($event->getData());
        foreach ($event->getEmbeddedEntityEvents() as $embedded_event) {
            $affected_attributes[] = $embedded_event->getParentAttributeName();
        }

        $attributes_to_update = [];
        $reference_filter_list = new CriteriaList([], CriteriaList::OP_OR);
        foreach ($this->getProjectionType()->getReferenceAttributes() as $attribute_path => $ref_attribute) {
            $mirror_attributes = [];
            foreach ($ref_attribute->getEmbeddedEntityTypeMap() as $reference_embed) {
                $referenced_type_impl = ltrim($reference_embed->getReferencedTypeClass(), '\\');
                if ($referenced_type_impl === $foreign_projection_type_impl) {
                    $attributes_to_mirror = $reference_embed->getAttributes()->filter(
                        function ($attribute) use ($affected_attributes) {
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
                $filter_field_path = sprintf('%s.referenced_identifier', $attribute_path);
                $reference_filter_list->push(
                    new AttributeCriteria(
                        $this->buildFieldFilterSpec($ref_attribute),
                        new Equals($event->getAggregateRootIdentifier())
                    )
                );
            }
        }

        $updated_entities = new EntityList;
        if (!empty($reference_filter_list)) {
            $filter_criteria_list = new CriteriaList;
            $filter_criteria_list->push(
                new AttributeCriteria('identifier', new Equals('!' . $event->getAggregateRootIdentifier()))
            );
            if ($reference_filter_list->getSize() === 1) {
                $filter_criteria_list->push($reference_filter_list->getFirst());
            } else {
                $filter_criteria_list->push($reference_filter_list);
            }
            $query_result = $this->getQueryService()->find(
                new Query(
                    new CriteriaList,
                    $filter_criteria_list,
                    new CriteriaList,
                    0,
                    100
                )
            );
            $entities_to_update = $query_result->getResults();

            foreach ($entities_to_update as $entity_to_update) {
                foreach ($attributes_to_update as $attribute_path => $mirror_attributes) {
                    $reference_embeds = AttributeValuePath::getAttributeValueByPath($entity_to_update, $attribute_path);
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
                $this->getStorageWriter()->write($entity_to_update);
                $updated_entities->push($entity_to_update);
            }
        }

        return $updated_entities;
    }

    protected function buildFieldFilterSpec(EmbeddedEntityListAttribute $embed_attribute)
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

    protected function getProjectionType()
    {
        $projection_type_prefix = $this->config->get('projection_type');
        if (!$this->projection_type_map->hasKey($projection_type_prefix)) {
            throw new RuntimeError('Unable to resolve projection-type for prefix: ' . $projection_type_prefix);
        }

        return $this->projection_type_map->getItem($projection_type_prefix);
    }

    protected function getQueryService()
    {
        $query_service_default = sprintf(
            '%s::query_service',
            $this->getProjectionType()->getPrefix()
        );

        $query_service_key = $this->config->get('query_service', $query_service_default);
        if (!$query_service_key || !$this->query_service_map->hasKey($query_service_key)) {
            throw new RuntimeError('Unable to resolve query_service for key: ' . $query_service_key);
        }

        return $this->query_service_map->getItem($query_service_key);
    }

    protected function getStorageWriter()
    {
        $projection_type = $this->getProjectionType();
        $storage_writer_default = sprintf(
            '%s::projection.standard::view_store::writer',
            $this->getProjectionType()->getPrefix()
        );

        $storage_writer_key = $this->config->get('storage_writer', $storage_writer_default);
        if (!$this->storage_writer_map->hasKey($storage_writer_key)) {
            throw new RuntimeError('Unable to resolve storage_writer for key: ' . $storage_writer_key);
        }

        return $this->storage_writer_map->getItem($storage_writer_key);
    }
}

<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Entity as BaseEntity;
use Honeybee\Model\Event\EmbeddedEntityEventInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\EmbeddedEntityModifiedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\EmbeddedEntityRemovedEvent;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\EmbeddedEntityAddedEvent;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;

abstract class Entity extends BaseEntity
{
    /**
     * Apply the given aggregate-event to it's corresponding aggregate and return the resulting source-event.
     *
     * @param EmbeddedEntityEventInterface $embedded_entity_event
     * @param boolean $auto_commit
     *
     * @return EmbeddedEntityEventInterface
     */
    protected function applyEmbeddedEntityEvent(
        EmbeddedEntityEventInterface $embedded_entity_event,
        $auto_commit = true
    ) {
        $attribute_name = $embedded_entity_event->getParentAttributeName();
        $embedded_entity_list = $this->getValue($attribute_name);

        if ($embedded_entity_event instanceof EmbeddedEntityAddedEvent) {
            $embedded_type = $this->getEmbeddedEntityTypeFor(
                $attribute_name,
                $embedded_entity_event->getEmbeddedEntityType()
            );
            $embedded_entity = $embedded_type->createEntity([], $this);
            $embedded_entity_list->push($embedded_entity);
        } elseif ($embedded_entity_event instanceof EmbeddedEntityRemovedEvent) {
            $embedded_entity = $this->getEmbeddedEntityFor(
                $attribute_name,
                $embedded_entity_event->getEmbeddedEntityIdentifier()
            );
            $embedded_entity_list->removeItem($embedded_entity);
            if (!$embedded_entity) {
                error_log(__METHOD__ . " - Embedded entity already was removed.");
                return $embedded_entity_event;
            }
        } elseif ($embedded_entity_event instanceof EmbeddedEntityModifiedEvent) {
            $embedded_entity = $this->getEmbeddedEntityFor(
                $attribute_name,
                $embedded_entity_event->getEmbeddedEntityIdentifier()
            );
            if (!$embedded_entity) {
                throw new RuntimeError(
                    'Unable to resolve embedded-entity for embed-event: ' .
                    json_encode($embedded_entity_event->toArray()) .
                    "\nAR-Id: " . $this->getIdentifier()
                );
            }
            if ($embedded_entity_list->getKey($embedded_entity) !== $embedded_entity_event->getPosition()) {
                $embedded_entity_list->moveTo($embedded_entity_event->getPosition(), $embedded_entity);
            }
        } else {
            throw new RuntimeError('Cannot resolve embedded entity');
        }

        return $embedded_entity->applyEvent($embedded_entity_event, $auto_commit);
    }

    /**
     * Return the AggregateType that is referred to by the given command.
     *
     * @param string $attribute_name
     * @param string $embedded_type_prefix
     *
     * @return EntityTypeInterface
     */
    protected function getEmbeddedEntityTypeFor($attribute_name, $embedded_type_prefix)
    {
        $attribute = $this->getType()->getAttribute($attribute_name);

        return $attribute->getEmbeddedTypeByPrefix($embedded_type_prefix);
    }

    /**
     * Return the Aggregate that is referred to by the given command.
     *
     * @param string $attribute_name
     * @param string $embedded_entity_id
     *
     * @return EntityInterface
     */
    protected function getEmbeddedEntityFor($attribute_name, $embedded_entity_id)
    {
        $found_entity = null;
        foreach ($this->getValue($attribute_name) as $embedded_entity) {
            if ($embedded_entity->getIdentifier() === $embedded_entity_id) {
                $found_entity = $embedded_entity;
                break;
            }
        }

        return $found_entity;
    }

    /**
     * Returns a list of changes that actually took place, while processing a given event.
     *
     * @return array
     */
    protected function getRecordedChanges()
    {
        $recorded_changes = [];

        foreach ($this->getChanges() as $value_changed_event) {
            $attribute = $this->getType()->getAttribute($value_changed_event->getAttributeName());
            if ($attribute instanceof EmbeddedEntityListAttribute) {
                continue;
            }
            $recorded_changes[$attribute->getName()] = $value_changed_event->getNewValue();
        }

        return $recorded_changes;
    }
}

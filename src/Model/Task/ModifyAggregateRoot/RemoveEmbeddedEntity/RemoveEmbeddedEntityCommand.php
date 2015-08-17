<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity;

use Honeybee\Model\Command\EmbeddedEntityCommand;
use Honeybee\Model\Event\EmbeddedEntityEventInterface;

class RemoveEmbeddedEntityCommand extends EmbeddedEntityCommand
{
    public static function getType()
    {
        return 'remove_embedded_entity';
    }

    public function getEventClass()
    {
        return EmbeddedEntityRemovedEvent::CLASS;
    }

    public function conflictsWith(EmbeddedEntityEventInterface $event, array &$conflicting_changes = [])
    {
        return $event->getEmbeddedEntityIdentifier() === $this->getEmbeddedEntityIdentifier();
    }

    public function getAffectedAttributeNames()
    {
        return [];
    }
}

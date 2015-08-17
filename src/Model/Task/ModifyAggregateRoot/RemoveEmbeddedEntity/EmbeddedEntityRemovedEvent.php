<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity;

use Honeybee\Model\Event\EmbeddedEntityEvent;

class EmbeddedEntityRemovedEvent extends EmbeddedEntityEvent
{
    public function getType()
    {
        return 'embedded_entity_removed';
    }
}

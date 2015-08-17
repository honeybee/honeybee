<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity;

use Honeybee\Model\Event\EmbeddedEntityEvent;

class EmbeddedEntityAddedEvent extends EmbeddedEntityEvent
{
    protected $position;

    public function getType()
    {
        return 'embedded_entity_added';
    }

    public function getPosition()
    {
        return $this->position;
    }
}

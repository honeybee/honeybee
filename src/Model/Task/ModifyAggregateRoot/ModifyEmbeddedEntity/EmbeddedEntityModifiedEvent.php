<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity;

use Honeybee\Model\Event\EmbeddedEntityEvent;

class EmbeddedEntityModifiedEvent extends EmbeddedEntityEvent
{
    protected $position;

    public function getType()
    {
        return 'embedded_entity_modified';
    }

    public function getPosition()
    {
        return $this->position;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert(is_int($this->position) && $this->position >= 0, 'position is correctly set');
    }
}

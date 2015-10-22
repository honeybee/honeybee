<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity;

use Assert\Assertion;
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

        Assertion::greaterOrEqualThan($this->position, 0);
    }
}

<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity;

use Honeybee\Model\Command\EmbeddedEntityTypeCommand;
use Honeybee\Model\Event\EmbeddedEntityEventInterface;

class AddEmbeddedEntityCommand extends EmbeddedEntityTypeCommand
{
    protected $values;

    protected $position;

    public static function getType()
    {
        return 'add_embedded_entity';
    }

    public function getEventClass()
    {
        return EmbeddedEntityAddedEvent::CLASS;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getAffectedAttributeNames()
    {
        return array_keys($this->values);
    }

    public function conflictsWith(EmbeddedEntityEventInterface $event, array &$conflicting_changes = [])
    {
        return false;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->values !== null, '"values" is set');
        assert(is_int($this->position) && $this->position >= 0, 'position is correctly set');
    }
}

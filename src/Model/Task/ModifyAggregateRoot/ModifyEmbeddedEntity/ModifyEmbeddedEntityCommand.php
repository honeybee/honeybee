<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity;

use Honeybee\Model\Command\EmbeddedEntityCommand;
use Honeybee\Model\Event\EmbeddedEntityEventInterface;

class ModifyEmbeddedEntityCommand extends EmbeddedEntityCommand
{
    protected $values;

    protected $position;

    public static function getType()
    {
        return 'modify_embedded_entity';
    }

    public function getEventClass()
    {
        return EmbeddedEntityModifiedEvent::CLASS;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getAffectedAttributeNames()
    {
        return array_keys($this->values);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function conflictsWith(EmbeddedEntityEventInterface $event, array &$conflicting_changes = [])
    {
        if ($event->getEmbeddedEntityIdentifier() !== $this->getEmbeddedEntityIdentifier()) {
            return false;
        }

        $conflict_detected = false;

        // @todo check against conflict with other modify event (resolvable) or remove (unresolvable)

        return $conflict_detected;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->values !== null, '"values" is set');
        assert(is_int($this->position) && $this->position >= 0, 'position is correctly set');
    }
}

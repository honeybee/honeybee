<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\Command;

abstract class EmbeddedEntityTypeCommand extends Command implements EmbeddedEntityTypeCommandInterface
{
    protected $embedded_entity_type;

    protected $parent_attribute_name;

    protected $embedded_entity_commands = [];

    public function getEmbeddedEntityType()
    {
        return $this->embedded_entity_type;
    }

    public function getEmbeddedEntityCommands()
    {
        return $this->embedded_entity_commands;
    }

    public function getParentAttributeName()
    {
        return $this->parent_attribute_name;
    }

    protected function setEmbeddedEntityCommands($embedded_entity_commands)
    {
        $this->embedded_entity_commands = [];

        foreach ($embedded_entity_commands as $aggregate_command) {
            if (!is_array($aggregate_command)) {
                $this->embedded_entity_commands[] = $aggregate_command;
            } else {
                $command_class = $aggregate_command[self::OBJECT_TYPE];
                $this->embedded_entity_commands[] = new $command_class($aggregate_command);
            }
        }
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->embedded_entity_type !== null, '"embedded_entity_type" is set');
        assert(is_array($this->embedded_entity_commands), '"embedded_entity_commands" should be an array');
    }
}

<?php

namespace Honeybee\Model\Command;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Command;

abstract class AggregateRootTypeCommand extends Command implements AggregateRootTypeCommandInterface
{
    protected $aggregate_root_type;

    protected $embedded_entity_commands;

    public function __construct(array $state = array())
    {
        $this->embedded_entity_commands = new EmbeddedEntityTypeCommandList;

        parent::__construct($state);
    }

    public function getAggregateRootType()
    {
        return $this->aggregate_root_type;
    }

    public function getEmbeddedEntityCommands()
    {
        return $this->embedded_entity_commands;
    }

    protected function setEmbeddedEntityCommands($embedded_entity_commands)
    {
        if ($embedded_entity_commands instanceof EmbeddedEntityTypeCommandList) {
            $this->embedded_entity_commands = $embedded_entity_commands;
        } elseif (is_array($embedded_entity_commands)) {
            $this->embedded_entity_commands = new EmbeddedEntityTypeCommandList;
            foreach ($embedded_entity_commands as $embedded_command_data) {
                $command_class = $embedded_command_data[self::OBJECT_TYPE];
                $this->embedded_entity_commands->push(new $command_class($embedded_command_data));
            }
        } else {
            throw new RuntimeError('Invalid type given as embedded_entity_commands property value.');
        }
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::classExists($this->aggregate_root_type);
        Assertion::notNull($this->embedded_entity_commands);
    }
}

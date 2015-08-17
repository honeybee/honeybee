<?php

namespace Honeybee\Model\Command;

use Honeybee\Infrastructure\Command\Command;

abstract class AggregateRootTypeCommand extends Command implements AggregateRootTypeCommandInterface
{
    protected $aggregate_root_type;

    protected $embedded_entity_commands;

    public function __construct(array $state = array())
    {
        $this->embedded_entity_commands = [];

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

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->getAggregateRootType() !== null, '"aggregate_root_type" is set');
        assert(is_array($this->getEmbeddedEntityCommands()), 'aggregate-commands type is correct');
    }
}

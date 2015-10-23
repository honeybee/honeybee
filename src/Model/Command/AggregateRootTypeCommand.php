<?php

namespace Honeybee\Model\Command;

use Assert\Assertion;
use Honeybee\Infrastructure\Command\Command;

abstract class AggregateRootTypeCommand extends Command implements AggregateRootTypeCommandInterface
{
    protected $aggregate_root_type;

    /**
     * @CommandBuilder::OPTIONAL
     */
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

        Assertion::classExists($this->aggregate_root_type);
        Assertion::isArray($this->embedded_entity_commands);
    }
}

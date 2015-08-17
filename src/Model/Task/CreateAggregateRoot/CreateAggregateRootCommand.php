<?php

namespace Honeybee\Model\Task\CreateAggregateRoot;

use Honeybee\Model\Command\AggregateRootTypeCommand;

abstract class CreateAggregateRootCommand extends AggregateRootTypeCommand
{
    protected $values;

    public function getValues()
    {
        return $this->values;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->values !== null, '"values" is set');
    }
}

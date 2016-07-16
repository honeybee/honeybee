<?php

namespace Honeybee\Model\Task\CreateAggregateRoot;

use Assert\Assertion;
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

        Assertion::isArray($this->values);
    }

    public function toArray()
    {
        return array_merge(
            parent::toArray(),
            [
                'values' => $this->values
            ]
        );
    }
}

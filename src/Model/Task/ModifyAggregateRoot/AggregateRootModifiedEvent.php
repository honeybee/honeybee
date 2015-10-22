<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot;

use Assert\Assertion;
use Honeybee\Model\Event\AggregateRootEvent;

abstract class AggregateRootModifiedEvent extends AggregateRootEvent
{
    protected $data;

    public function getData()
    {
        return $this->data;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::isArray($this->data);
    }
}

<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot;

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

        assert(is_array($this->data), 'data is an array');
    }
}

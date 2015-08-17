<?php

namespace Honeybee\Model\Task\CreateAggregateRoot;

use Honeybee\Model\Event\AggregateRootEvent;

abstract class AggregateRootCreatedEvent extends AggregateRootEvent
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

        $mandatory_attribute_names = [ 'identifier', 'uuid', 'language', 'version', 'workflow_state' ];

        foreach ($mandatory_attribute_names as $attribute_name) {
            assert(array_key_exists($attribute_name, $this->data), sprintf('%s is set into data', $attribute_name));
        }
    }
}

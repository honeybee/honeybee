<?php

namespace Honeybee\Model\Task\CreateAggregateRoot;

use Assert\Assertion;
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

        Assertion::isArray($this->data);
        $mandatory_attribute_names = [ 'identifier', 'uuid', 'language', 'version', 'workflow_state' ];
        foreach ($mandatory_attribute_names as $attribute_name) {
            Assertion::keyExists($this->data, $attribute_name, 'mandatory');
        }
    }
}

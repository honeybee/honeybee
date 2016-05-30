<?php

namespace Honeybee\Model\Task\MoveAggregateRootNode;

use Assert\Assertion;
use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;

abstract class AggregateRootNodeMovedEvent extends AggregateRootModifiedEvent
{
    public function getParentNodeId()
    {
        return $this->data['parent_node_id'];
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::keyExists($this->data, 'parent_node_id');
        Assertion::string($this->data['parent_node_id']);
        if ($this->data['parent_node_id'] !== '') {
            Assertion::regex(
                $this->data['parent_node_id'],
                '/[\w\.\-_]{1,128}\-\w{8}\-\w{4}\-\w{4}\-\w{4}\-\w{12}\-\w{2}_\w{2}\-\d+/'
            );
        }
    }
}

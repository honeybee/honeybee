<?php

namespace Honeybee\Model\Task\MoveAggregateRootNode;

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

        assert(isset($this->data['parent_node_id']), 'parent-node-id is set');
    }
}

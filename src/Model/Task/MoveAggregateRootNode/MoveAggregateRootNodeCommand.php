<?php

namespace Honeybee\Model\Task\MoveAggregateRootNode;

use Honeybee\Model\Command\AggregateRootCommand;
use Honeybee\Model\Event\AggregateRootEventInterface;

abstract class MoveAggregateRootNodeCommand extends AggregateRootCommand
{
    protected $parent_node_id;

    public function getAffectedAttributeNames()
    {
        return 'parent_node_id';
    }

    public function getParentNodeId()
    {
        return $this->parent_node_id;
    }

    public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = [])
    {
        if ($event->getAggregateRootIdentifier() !== $this->getAggregateRootIdentifier()) {
            return false;
        }

        if ($event instanceof AggregateRootNodeMovedEvent) {
            if ($event->getParentNodeId() !== $this->getParentNodeId()) {
                $conflicting_changes['parent_node_id'] = $event->getParentNodeId();
                return true;
            }
        }

        return false;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->parent_node_id !== null, '"parent_node_id" is set');
    }
}

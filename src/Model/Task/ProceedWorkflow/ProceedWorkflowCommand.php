<?php

namespace Honeybee\Model\Task\ProceedWorkflow;

use Honeybee\Model\Command\AggregateRootCommand;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Honeybee\Model\Event\AggregateRootEventInterface;

abstract class ProceedWorkflowCommand extends AggregateRootCommand
{
    protected $current_state_name;

    protected $event_name;

    public function getAffectedAttributeNames()
    {
        return [ 'execution_context' ];
    }

    public function getCurrentStateName()
    {
        return $this->current_state_name;
    }

    public function getEventName()
    {
        return $this->event_name;
    }

    public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = [])
    {
        return $event->getAggregateRootIdentifier() === $this->getAggregateRootIdentifier()
            && $event instanceof WorkflowProceededEvent;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->event_name !== null, '"event_name" is set');
        assert($this->current_state_name !== null, '"current_state_name" is set');
    }
}

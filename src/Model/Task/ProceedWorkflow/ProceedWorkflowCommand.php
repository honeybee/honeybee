<?php

namespace Honeybee\Model\Task\ProceedWorkflow;

use Assert\Assertion;
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

        Assertion::string($this->event_name);
        Assertion::string($this->current_state_name);
    }
}

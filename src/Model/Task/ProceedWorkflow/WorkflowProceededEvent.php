<?php

namespace Honeybee\Model\Task\ProceedWorkflow;

use Assert\Assertion;
use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;

abstract class WorkflowProceededEvent extends AggregateRootModifiedEvent
{
    public function getWorkflowState()
    {
        return $this->data['workflow_state'];
    }

    public function getWorkflowParameters()
    {
        return isset($this->data['workflow_parameters']) ? $this->data['workflow_parameters'] : [];
    }

    public function getPreviousState()
    {
        $workflow_parameters = $this->getWorkflowParameters();
        return isset($workflow_parameters['previous_state']) ? $workflow_parameters['previous_state'] : '';
    }

    public function getWorkflowEvent()
    {
        $workflow_parameters = $this->getWorkflowParameters();
        return isset($workflow_parameters['workflow_event']) ? $workflow_parameters['workflow_event'] : '';
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::keyExists($this->data, 'workflow_state');
        Assertion::string($this->data['workflow_state']);
    }
}

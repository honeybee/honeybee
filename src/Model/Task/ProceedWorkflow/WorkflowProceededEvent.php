<?php

namespace Honeybee\Model\Task\ProceedWorkflow;

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

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert(isset($this->data['workflow_state']), 'workflow-state is set');
    }
}

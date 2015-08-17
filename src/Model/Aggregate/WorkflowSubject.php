<?php

namespace Honeybee\Model\Aggregate;

use Workflux\StatefulSubjectInterface;
use Workflux\ExecutionContext;

class WorkflowSubject implements StatefulSubjectInterface
{
    protected $execution_context;

    protected $aggregate_root;

    public function __construct($state_machine_name, AggregateRootInterface $aggregate_root)
    {
        $this->aggregate_root = $aggregate_root;

        $this->execution_context = new ExecutionContext(
            $state_machine_name,
            $this->aggregate_root->getWorkflowState(),
            array_merge(
                $aggregate_root->getWorkflowParameters(),
                [ 'current_state' => $this->aggregate_root->getWorkflowState() ]
            )
        );
    }

    public function getExecutionContext()
    {
        return $this->execution_context;
    }

    public function getCurrentStateName()
    {
        return $this->execution_context->getCurrentStateName();
    }

    public function getAggregateRoot()
    {
        return $this->aggregate_root;
    }

    public function getWorkflowParameters()
    {
        $parameters = $this->execution_context->getParameters()->toArray();
        if (isset($parameters['current_state'])) {
            unset($parameters['current_state']);
        }

        return $parameters;
    }
}

<?php

namespace Honeybee\Projection;

use Honeybee\Projection\ProjectionInterface;
use Workflux\ExecutionContext;
use Workflux\StatefulSubjectInterface;

class WorkflowSubject implements StatefulSubjectInterface
{
    protected $execution_context;

    protected $resource;

    public function __construct($state_machine_name, ProjectionInterface $resource)
    {
        $this->resource = $resource;

        $this->execution_context = new ExecutionContext(
            $state_machine_name,
            $resource->getWorkflowState(),
            array_merge(
                $resource->getWorkflowParameters(),
                [ 'current_state' => $resource->getWorkflowState() ]
            )
        );
    }

    public function getExecutionContext()
    {
        return $this->execution_context;
    }

    public function getResource()
    {
        return $this->resource;
    }
}

<?php

namespace Honeybee\Projection;

use Workflux\StateMachine\StateMachineInterface;

interface ProjectionTypeInterface
{
    /**
     * @return StateMachineInterface
     */
    public function getWorkflowStateMachine();
}

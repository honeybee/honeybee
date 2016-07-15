<?php

namespace Honeybee\Projection;

use Workflux\StateMachine\StateMachineInterface;

interface ProjectionTypeInterface
{
    const DEFAULT_VARIANT = 'Standard';

    /**
     * @return StateMachineInterface
     */
    public function getWorkflowStateMachine();
}

<?php

namespace Honeybee\Projection;

use Trellis\EntityType\EntityTypeInterface;
use Workflux\StateMachine\StateMachineInterface;

interface ProjectionTypeInterface extends EntityTypeInterface
{
    /**
     * @return StateMachineInterface
     */
    public function getWorkflowStateMachine();
}

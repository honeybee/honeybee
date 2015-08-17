<?php

namespace Honeybee\Model\Aggregate;

use Workflux\StateMachine\StateMachineInterface;

interface AggregateRootTypeInterface
{
    /**
     * @return StateMachineInterface
     */
    public function getWorkflowStateMachine();
}

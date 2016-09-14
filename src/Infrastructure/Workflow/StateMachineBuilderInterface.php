<?php

namespace Honeybee\Infrastructure\Workflow;

interface StateMachineBuilderInterface
{
    /**
     * @return Workflux\StateMachine\StateMachineInterface
     */
    public function build($name);
}

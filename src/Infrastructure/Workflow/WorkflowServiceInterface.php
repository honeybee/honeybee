<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Projection\ProjectionInterface;
use Workflux\StateMachine\StateMachineInterface;

interface WorkflowServiceInterface
{
    public function getStateMachine($name);

    public function getTaskByStateAndEvent(StateMachineInterface $state_machine, ProjectionInterface $resource, $event);

    public function getSupportedEventsFor(StateMachineInterface $state_machine, $state_name, $write_only = false);

    public function getWriteEventNames();
}

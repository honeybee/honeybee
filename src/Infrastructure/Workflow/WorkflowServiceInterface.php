<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Projection\ProjectionInterface;
use Workflux\StateMachine\StateMachineInterface;

interface WorkflowServiceInterface
{
    /**
     * Builds and returns the state machine for the given name. As a name an object the resolveStateMachineName()
     * method supports can be given.
     *
     * @param mixed $name name of state machine to lookup or build and return
     *
     * @return StateMachineInterface state machine found/built
     */
    public function getStateMachine($name);

    /**
     * Resolves a name for the given item that can be used to build or lookup an already built state machine.
     *
     * @param mixed $arg object to resolve a name for
     *
     * @return string name to use for state machine lookups/building
     *
     * @throws RuntimeError when no name can be resolved
     */
    public function resolveStateMachineName($arg);

    public function getTaskByStateAndEvent(StateMachineInterface $state_machine, ProjectionInterface $resource, $event);

    public function getSupportedEventsFor(StateMachineInterface $state_machine, $state_name, $write_only = false);

    public function getWriteEventNames();
}

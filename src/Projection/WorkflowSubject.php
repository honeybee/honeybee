<?php

namespace Honeybee\Projection;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Projection\ProjectionInterface;
use Workflux\ExecutionContext;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineInterface;
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

    public static function getTaskByStateAndEvent(
        StateMachineInterface $state_machine,
        ProjectionInterface $resource,
        $event
    ) {
        $workflow_subject = new WorkflowSubject($state_machine->getName(), $resource);
        $state_machine->execute($workflow_subject, $event);
        $workflow_context = $workflow_subject->getExecutionContext();

        if (!$workflow_context->hasParameter('task_action')) {
            throw new RuntimeError('Expected "task_action" parameter to be set after workflow execution.');
        }

        return $workflow_context->getParameter('task_action')->toArray();
    }

    public static function getSupportedEventsFor(StateMachineInterface $state_machine, $state_name, $write_only = false)
    {
        $write_events = self::getWriteEventNames();

        return array_filter(
            array_keys($state_machine->getTransitions($state_name)),
            function ($event_name) use ($write_events, $write_only) {
                if ($event_name === StateMachine::SEQ_TRANSITIONS_KEY) {
                    return false;
                }
                if ($write_only && !in_array($event_name, $write_events)) {
                    return false;
                }
                return true;
            }
        );
    }

    public static function getWriteEventNames()
    {
        return [ 'promote', 'demote', 'delete' ];
    }
}

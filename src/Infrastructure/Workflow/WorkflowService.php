<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\WorkflowSubject;
use Psr\Log\LoggerInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Projection\ProjectionTypeInterface;

class WorkflowService implements WorkflowServiceInterface
{
    protected $config;

    protected $logger;

    protected $state_machine_builder;

    public function __construct(
        ConfigInterface $config,
        StateMachineBuilderInterface $state_machine_builder,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->state_machine_builder = $state_machine_builder;
        $this->logger = $logger;
    }

    /**
     * Builds and returns the state machine for the given name. As a name an object the resolveStateMachineName()
     * method supports can be given.
     *
     * @param mixed $name name of state machine to build and return.
     *
     * @return StateMachineInterface state machine built/found
     */
    public function getStateMachine($name)
    {
        return $this->state_machine_builder->build($this->resolveStateMachineName($name));
    }

    /**
     * Resolves a name for the given item that can be used to build or lookup an already built state machine.
     *
     * @param mixed $arg object to resolve a name for
     *
     * @return string name to use for state machine lookups/building
     *
     * @throws RuntimeError when no name can be resolved
     */
    public function resolveStateMachineName($arg)
    {
        if (is_string($arg) && empty(trim($arg))) {
            throw new RuntimeError('State machine name must be a non-empty string.');
        }

        if (is_string($arg)) {
            return $arg;
        }

        $default_suffix = $this->config->get('state_machine_name_suffix', 'default_workflow');

        if ($arg instanceof AggregateRootTypeInterface || $arg instanceof ProjectionTypeInterface) {
            return $arg->getPrefix() . '.' . $default_suffix;
        } elseif ($arg instanceof AggregateRootInterface || $arg instanceof ProjectionInterface) {
            return $arg->getType()->getPrefix() . '.' . $default_suffix;
        } elseif (is_object($arg) && is_callable($arg, 'getStateMachineName')) {
            return $arg->getStateMachineName();
        } elseif (is_object($arg) && is_callable($arg, '__toString')) {
            return (string)$arg;
        }

        throw new RuntimeError('Could not resolve a state machine name for given argument.');
    }

    public function getTaskByStateAndEvent(
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

    public function getSupportedEventsFor(StateMachineInterface $state_machine, $state_name, $write_only = false)
    {
        $write_events = $this->getWriteEventNames();

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

    public function getWriteEventNames()
    {
        return (array)$this->config->get('write_event_names', [ 'promote', 'demote', 'delete' ]);
    }
}

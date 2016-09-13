<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Expression\ExpressionServiceInterface;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\WorkflowSubject;
use Psr\Log\LoggerInterface;
use Workflux\StateMachine\StateMachine;
use Workflux\StateMachine\StateMachineInterface;

class WorkflowService implements WorkflowServiceInterface
{
    protected $config;

    protected $logger;

    protected $expression_service;

    public function __construct(
        ConfigInterface $config,
        ExpressionServiceInterface $expression_service,
        StateMachineBuilderInterface $state_machine_builder,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->expression_service = $expression_service;
        $this->service_locator = $service_locator;
        $this->logger = $logger;
    }

    public function getStateMachine($name)
    {
        return $this->state_machine_builder->build($name);
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
        return $this->config->get('write_event_names', [ 'promote', 'demote', 'delete' ]);
    }
}

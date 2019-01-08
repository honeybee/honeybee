<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;
use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;
use Workflux\StateMachine\StateMachineInterface;

interface AggregateRootInterface
{
    public function markAsComitted();

    public function getUncomittedEvents();

    /**
     * Return a list of events that have occured in the past.
     *
     * @return AggregateRootEventList
     */
    public function getHistory();

    public function reconstituteFrom(AggregateRootEventList $history);

    /**
     * Start a new life-cycle for the current aggregate-root.
     *
     * @param CreateAggregateRootCommand $create_command
     * @param StateMachineInterface $state_machine
     */
    public function create(CreateAggregateRootCommand $create_command, StateMachineInterface $state_machine);

    public function modify(ModifyAggregateRootCommand $modify_command);

    /**
     * Transition to the next workflow state (next state of the state machine based on the command paylaod).
     *
     * @param ProceedWorkflowCommand $workflow_command
     * @param StateMachineInterface $state_machine
     */
    public function proceedWorkflow(ProceedWorkflowCommand $workflow_command, StateMachineInterface $state_machine);

    public function getUuid();

    public function getLanguage();

    public function getVersion();

    public function getRevision();

    public function getWorkflowState();

    public function getWorkflowParameters();

    public function __toString();
}

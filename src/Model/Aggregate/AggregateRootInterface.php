<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Task\CreateAggregateRoot\CreateAggregateRootCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;
use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;

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

    public function create(CreateAggregateRootCommand $create_command);

    public function modify(ModifyAggregateRootCommand $modify_command);

    public function proceedWorkflow(ProceedWorkflowCommand $workflow_command);

    public function getUuid();

    public function getLanguage();

    public function getVersion();

    /**
     * @return int
     */
    public function getRevision();

    public function getWorkflowState();

    public function getWorkflowParameters();

    public function __toString();
}

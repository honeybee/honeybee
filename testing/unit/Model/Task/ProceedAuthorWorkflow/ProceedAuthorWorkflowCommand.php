<?php

namespace Honeybee\Tests\Model\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class ProceedAuthorWorkflowCommand extends ProceedWorkflowCommand
{
    public function getEventClass()
    {
        return AuthorWorkflowProceededEvent::CLASS;
    }

    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}

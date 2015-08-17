<?php

namespace Honeybee\Tests\Model\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Honeybee\Tests\Model\Aggregate\Author\AuthorType;

class AuthorWorkflowProceededEvent extends WorkflowProceededEvent
{
    public function getAggregateRootType()
    {
        return AuthorType::CLASS;
    }
}

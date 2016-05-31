<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;

class AuthorWorkflowProceededEvent extends WorkflowProceededEvent
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }
}

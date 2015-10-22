<?php

namespace Honeybee\Tests\Model\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;
use Honeybee\Tests\Model\Aggregate\Fixtures\Author\AuthorType;

class ProceedAuthorWorkflowCommand extends ProceedWorkflowCommand
{
    public function __construct(array $state)
    {
        $state['aggregate_root_type'] = AuthorType::CLASS;

        parent::__construct($state);
    }

    public function getEventClass()
    {
        return AuthorWorkflowProceededEvent::CLASS;
    }
}

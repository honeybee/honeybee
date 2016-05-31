<?php

namespace Honeybee\Tests\Fixture\BookSchema\Task\ProceedAuthorWorkflow;

use Honeybee\Model\Task\ProceedWorkflow\ProceedWorkflowCommand;
use Honeybee\Tests\Fixture\BookSchema\Model\Author\AuthorType;

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

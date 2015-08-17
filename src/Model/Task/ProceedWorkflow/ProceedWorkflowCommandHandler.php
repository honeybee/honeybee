<?php

namespace Honeybee\Model\Task\ProceedWorkflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Command\AggregateRootCommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;

class ProceedWorkflowCommandHandler extends AggregateRootCommandHandler
{
    protected function doExecute(CommandInterface $proceed_workflow_command, AggregateRootInterface $aggregate_root)
    {
        if (!$proceed_workflow_command instanceof ProceedWorkflowCommand) {
            throw new RuntimeError(
                sprintf(
                    'The %s only supports events that descend from %s',
                    static::CLASS,
                    ProceedWorkflowCommand::CLASS
                )
            );
        }

        return $aggregate_root->proceedWorkflow($proceed_workflow_command);
    }
}

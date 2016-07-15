<?php

namespace Honeybee\Model\Task\CreateAggregateRoot;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Command\AggregateRootCommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;

class CreateAggregateRootCommandHandler extends AggregateRootCommandHandler
{
    protected function doExecute(CommandInterface $create_command, AggregateRootInterface $aggregate_root)
    {
        if (!$create_command instanceof CreateAggregateRootCommand) {
            throw new RuntimeError(
                sprintf(
                    'The %s only supports events that descend from %s',
                    static::CLASS,
                    CreateAggregateRootCommand::CLASS
                )
            );
        }

        return $aggregate_root->create($create_command);
    }
}

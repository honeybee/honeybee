<?php

namespace Honeybee\Model\Task\ModifyAggregateRoot;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Command\AggregateRootCommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;

class ModifyAggregateRootCommandHandler extends AggregateRootCommandHandler
{
    protected function doExecute(CommandInterface $modify_command, AggregateRootInterface $aggregate_root)
    {
        if (!$modify_command instanceof ModifyAggregateRootCommand) {
            throw new RuntimeError(
                sprintf(
                    'The %s only supports events that descend from %s',
                    static::CLASS,
                    ModifyAggregateRootCommand::CLASS
                )
            );
        }

        return $aggregate_root->modify($modify_command);
    }
}

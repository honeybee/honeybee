<?php

namespace Honeybee\Model\Task\MoveAggregateRootNode;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Command\AggregateRootCommandHandler;
use Honeybee\Infrastructure\Command\CommandInterface;

class MoveAggregateRootNodeCommandHandler extends AggregateRootCommandHandler
{
    protected function doExecute(CommandInterface $move_node_command, AggregateRootInterface $aggregate_root)
    {
        if (!$move_node_command instanceof MoveAggregateRootNodeCommand) {
            throw new RuntimeError(
                sprintf(
                    'The %s only supports events that inherit from %s',
                    static::CLASS,
                    MoveAggregateRootNodeCommand::CLASS
                )
            );
        }

        return $aggregate_root->moveNode($move_node_command);
    }
}

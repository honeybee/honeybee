<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Shrink0r\Monatic\Success;
use Shrink0r\Monatic\Error;

class CommandBuilderList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return CommandBuilderInterface::CLASS;
    }

    public function build()
    {
        $errors = [];
        $add_commands = [];
        $modify_commands = [];
        $remove_commands = [];

        // build all commands
        foreach ($this->items as $command_builder) {
            $result = $command_builder->build();
            if ($result instanceof Success) {
                $command = $result->get();
                switch (true) {
                    case $command instanceof AddEmbeddedEntityCommand:
                        $add_commands[] = $command;
                        break;
                    case $command instanceof ModifyEmbeddedEntityCommand:
                        $modify_commands[] = $command;
                        break;
                    case $command instanceof RemoveEmbeddedEntityCommand:
                        $remove_commands[] = $command;
                        break;
                    default:
                        throw new RuntimeError(sprintf('Unknown command type "%s"', get_class($command)));
                }
            } elseif ($result instanceof Error) {
                $errors[$command_builder->getParentAttributeName()][] = $result->get();
            }
        }

        if (empty($errors)) {
            $result = Success::unit(array_merge($modify_commands, $remove_commands, $add_commands));
        } else {
            $result = Error::unit($errors);
        }

        return $result;
    }
}

<?php

namespace Honeybee\Infrastructure\Job\Bundle;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Job\Job;

class ExecuteCommandJob extends Job
{
    protected $command;

    /**
     * @hiddenProperty
     */
    protected $command_bus;

    public function __construct(CommandBusInterface $command_bus, array $state)
    {
        parent::__construct($state);

        $this->command_bus = $command_bus;
    }

    public function run(array $parameters = [])
    {
        $this->command_bus->execute($this->command);
    }

    protected function setCommand($command_state)
    {
        if (is_array($command_state)) {
            if (!isset($command_state[self::OBJECT_TYPE])) {
                throw new RuntimeError("Unable to create command without type information.");
            }

            $command_implementor = $command_state[self::OBJECT_TYPE];
            if (!class_exists($command_implementor)) {
                throw new RuntimeError("Unable to resolve command implementor: " . $command_implementor);
            }

            $this->command = new $command_implementor($command_state);
        } elseif ($command_state instanceof CommandInterface) {
            $this->command = $command_state;
        } else {
            throw new RuntimeError("Invalid command data-type given. Only array and CommandInterface are supported.");
        }
    }
}

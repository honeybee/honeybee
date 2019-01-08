<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\Transport\CommandTransportInterface;
use Honeybee\Infrastructure\Command\CommandHandlerInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class LazyCommandSubscription extends CommandSubscription
{
    protected $command_handler_callback;

    public function __construct(
        $command_type,
        Closure $command_handler_callback,
        CommandTransportInterface $command_transport,
        SettingsInterface $settings
    ) {
        $this->command_type = $command_type;
        $this->command_transport = $command_transport;
        $this->command_handler_callback = $command_handler_callback;
        $this->settings = $settings;
    }

    public function getCommandHandler()
    {
        if (!$this->command_handler) {
            $this->command_handler = $this->createCommandHandler();
        }
        return $this->command_handler;
    }

    protected function createCommandHandler()
    {
        $create_function = $this->command_handler_callback;
        $command_handler = $create_function();

        if (!is_object($command_handler)) {
            throw new RuntimeError(
                sprintf(
                    "Non-object provided as command handler, expected instance of %s",
                    CommandHandlerInterface::CLASS
                )
            );
        } elseif (!$command_handler instanceof CommandHandlerInterface) {
            throw new RuntimeError(
                sprintf(
                    "Invalid command handler type given: %s, expected instance of %s",
                    get_class($command_handler),
                    CommandHandlerInterface::CLASS
                )
            );
        }

        return $command_handler;
    }
}

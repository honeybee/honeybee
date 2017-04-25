<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class SynchronousTransport extends CommandTransport
{
    protected $command_bus;

    public function __construct($name, CommandBusInterface $command_bus)
    {
        parent::__construct($name);

        $this->command_bus = $command_bus;
    }

    public function send(CommandInterface $command, SettingsInterface $settings = null)
    {
        $this->command_bus->execute($command);
    }
}

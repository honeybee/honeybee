<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;

class SynchronousTransport extends CommandTransport
{
    protected $command_bus;

    public function __construct($name, CommandBusInterface $command_bus)
    {
        parent::__construct($name);

        $this->command_bus = $command_bus;
    }

    public function send(CommandInterface $command)
    {
        $this->command_bus->execute($command);
    }
}

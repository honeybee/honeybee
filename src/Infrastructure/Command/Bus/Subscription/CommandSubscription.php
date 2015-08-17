<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Trellis\Common\Object;
use Honeybee\Infrastructure\Command\Bus\Transport\CommandTransportInterface;
use Honeybee\Infrastructure\Command\CommandHandlerInterface;

class CommandSubscription extends Object implements CommandSubscriptionInterface
{
    protected $command_type;

    protected $command_transport;

    protected $command_handler;

    public function __construct(
        $command_type,
        CommandHandlerInterface $command_handler,
        CommandTransportInterface $command_transport
    ) {
        $this->command_type = $command_type;
        $this->command_transport = $command_transport;
        $this->command_handler = $command_handler;
    }

    public function getCommandType()
    {
        return $this->command_type;
    }

    public function getCommandHandler()
    {
        return $this->command_handler;
    }

    public function getCommandTransport()
    {
        return $this->command_transport;
    }
}

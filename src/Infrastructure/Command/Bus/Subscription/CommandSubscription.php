<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Honeybee\Infrastructure\Command\Bus\Transport\CommandTransportInterface;
use Honeybee\Infrastructure\Command\CommandHandlerInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Trellis\Common\Object;

class CommandSubscription extends Object implements CommandSubscriptionInterface
{
    protected $command_type;

    protected $command_transport;

    protected $command_handler;

    protected $settings;

    public function __construct(
        $command_type,
        CommandHandlerInterface $command_handler,
        CommandTransportInterface $command_transport,
        SettingsInterface $settings
    ) {
        $this->command_type = $command_type;
        $this->command_transport = $command_transport;
        $this->command_handler = $command_handler;
        $this->settings = $settings;
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

    public function getSettings()
    {
        return $this->settings;
    }
}

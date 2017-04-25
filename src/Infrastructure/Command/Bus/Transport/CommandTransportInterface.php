<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

interface CommandTransportInterface
{
    public function send(CommandInterface $command, SettingsInterface $settings = null);

    public function getName();
}

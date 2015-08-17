<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Honeybee\Infrastructure\Command\CommandInterface;

interface CommandTransportInterface
{
    public function send(CommandInterface $command);

    public function getName();
}

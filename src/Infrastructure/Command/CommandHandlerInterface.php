<?php

namespace Honeybee\Infrastructure\Command;

interface CommandHandlerInterface
{
    public function execute(CommandInterface $command);
}

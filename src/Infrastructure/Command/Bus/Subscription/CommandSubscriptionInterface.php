<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

interface CommandSubscriptionInterface
{
    public function getCommandType();

    public function getCommandHandler();

    public function getCommandTransport();
}

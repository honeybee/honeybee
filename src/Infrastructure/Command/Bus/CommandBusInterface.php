<?php

namespace Honeybee\Infrastructure\Command\Bus;

use Honeybee\Infrastructure\Command\Bus\Subscription\CommandSubscriptionInterface;
use Honeybee\Infrastructure\Command\CommandInterface;

interface CommandBusInterface
{
    public function execute(CommandInterface $command);

    public function post(CommandInterface $command);

    public function subscribe(CommandSubscriptionInterface $route);

    public function unsubscribe(CommandSubscriptionInterface $route);
}

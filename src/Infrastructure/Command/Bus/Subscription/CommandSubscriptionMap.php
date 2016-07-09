<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class CommandSubscriptionMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $command_subscriptions = [])
    {
        parent::__construct(CommandSubscriptionInterface::CLASS, $command_subscriptions);
    }
}

<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedMap;

class CommandSubscriptionMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return CommandSubscriptionInterface::CLASS;
    }
}

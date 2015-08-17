<?php

namespace Honeybee\Infrastructure\Command\Bus\Subscription;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedMap;

class CommandSubscriptionMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return CommandSubscriptionInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EventSubscriptionList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EventSubscriptionInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EventSubscriptionList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EventSubscriptionInterface::CLASS;
    }
}

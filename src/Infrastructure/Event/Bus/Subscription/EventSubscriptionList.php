<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EventSubscriptionList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $subscriptions = [])
    {
        parent::__construct(EventSubscriptionInterface::CLASS, $subscriptions);
    }
}

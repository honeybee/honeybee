<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EventFilterList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $event_filters = [])
    {
        parent::__construct(EventFilterInterface::CLASS, $event_filters);
    }
}

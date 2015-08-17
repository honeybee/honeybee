<?php

namespace Honeybee\Model\Event;

use Honeybee\Infrastructure\Event\EventList;

class AggregateRootEventList extends EventList
{
    protected function getItemImplementor()
    {
        return AggregateRootEventInterface::CLASS;
    }
}

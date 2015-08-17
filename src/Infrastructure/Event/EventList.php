<?php

namespace Honeybee\Infrastructure\Event;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EventList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EventInterface::CLASS;
    }
}

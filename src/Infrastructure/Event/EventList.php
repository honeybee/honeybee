<?php

namespace Honeybee\Infrastructure\Event;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EventList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EventInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Event;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EventList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $events = [])
    {
        parent::__construct(EventInterface::CLASS, $events);
    }
}

<?php

namespace Honeybee\Infrastructure\Event;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EventHandlerList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $event_handlers = [])
    {
        parent::__construct(EventHandlerInterface::CLASS, $event_handlers);
    }
}

<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedMap;

class EventStreamMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EventStream::CLASS;
    }
}

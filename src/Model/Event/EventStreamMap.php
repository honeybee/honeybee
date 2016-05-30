<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;

class EventStreamMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EventStream::CLASS;
    }
}

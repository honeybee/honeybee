<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedMap;

class EventStreamMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EventStream::CLASS;
    }
}

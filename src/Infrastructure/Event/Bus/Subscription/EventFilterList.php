<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EventFilterList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EventFilterInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EventFilterList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EventFilterInterface::CLASS;
    }
}

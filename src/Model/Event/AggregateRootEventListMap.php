<?php

namespace Honeybee\Model\Event;

use Honeybee\Model\Event\AggregateRootEventList;
use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedMap;

class AggregateRootEventListMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return AggregateRootEventList::CLASS;
    }
}

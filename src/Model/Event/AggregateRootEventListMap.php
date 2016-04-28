<?php

namespace Honeybee\Model\Event;

use Honeybee\Model\Event\AggregateRootEventList;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedMap;

class AggregateRootEventListMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return AggregateRootEventList::CLASS;
    }
}

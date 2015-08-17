<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Common\Collection\TypedList;

class AggregateRootList extends TypedList
{
    protected function getItemImplementor()
    {
        return AggregateRootInterface::CLASS;
    }
}

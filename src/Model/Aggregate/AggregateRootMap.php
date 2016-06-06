<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\Entity\EntityMap;

class AggregateRootMap extends EntityMap
{
    protected function getItemImplementor()
    {
        return AggregateRootInterface::CLASS;
    }

    /**
     * Convert the map to a list
     *
     * @return AggregateRootList
     */
    public function toList()
    {
        return new AggregateRootList($this->items);
    }
}

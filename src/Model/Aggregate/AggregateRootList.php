<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\Entity\EntityList;

class AggregateRootList extends EntityList
{
    protected function getItemImplementor()
    {
        return AggregateRootInterface::CLASS;
    }

    /**
     * Convert the list to a map
     *
     * @return AggregateRootMap
     */
    public function toMap()
    {
        return new AggregateRootMap($this->items);
    }
}

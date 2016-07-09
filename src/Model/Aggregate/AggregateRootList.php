<?php

namespace Honeybee\Model\Aggregate;

use Trellis\EntityType\Attribute\EntityList\EntityList;

class AggregateRootList extends EntityList
{
    public function __construct(array $aggregate_roots = [])
    {
        parent::__construct($aggregate_roots, AggregateRootInterface::CLASS);
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

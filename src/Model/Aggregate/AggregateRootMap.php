<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class AggregateRootMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $aggregate_roots = [])
    {
        parent::__construct(AggregateRootInterface::CLASS, $aggregate_roots);
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

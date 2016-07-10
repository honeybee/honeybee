<?php

namespace Honeybee\Model\Aggregate;

use Assert\Assertion;
use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class AggregateRootMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $aggregate_roots = [])
    {
        $mapped_agg_roots = [];
        foreach ($aggregate_roots as $aggregate_root) {
            Assertion::isInstanceOf($aggregate_root, AggregateRootInterface::CLASS);
            $mapped_agg_roots[$aggregate_root->getIdentifier()] = $aggregate_root;
        }

        parent::__construct(AggregateRootInterface::CLASS, $mapped_agg_roots);
    }

    /**
     * Convert the map to a list
     *
     * @return AggregateRootList
     */
    public function toList()
    {
        return new AggregateRootList(array_values($this->items));
    }
}

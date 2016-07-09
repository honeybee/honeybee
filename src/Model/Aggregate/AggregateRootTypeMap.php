<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class AggregateRootTypeMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $aggregate_root_types = [])
    {
        parent::__construct(AggregateRootType::CLASS, $aggregate_root_types);
    }
}

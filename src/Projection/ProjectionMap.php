<?php

namespace Honeybee\Projection;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ProjectionMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $projections = [])
    {
        parent::__construct(ProjectionInterface::CLASS, $projections);
    }

    /**
     * Convert the map to a list
     *
     * @return ProjectionList
     */
    public function toList()
    {
        return new ProjectionList($this->items);
    }
}

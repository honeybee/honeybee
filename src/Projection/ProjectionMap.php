<?php

namespace Honeybee\Projection;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ProjectionMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $projection_types = [])
    {
        parent::__construct(ProjectionInterface::CLASS, $projection_types);
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

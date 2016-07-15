<?php

namespace Honeybee\Projection;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class ProjectionList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $projection_types = [])
    {
        parent::__construct(ProjectionInterface::CLASS, $projection_types);
    }

    /**
     * Convert the list to a map
     *
     * @return ProjectionMap
     */
    public function toMap()
    {
        return new ProjectionMap($this->items);
    }
}

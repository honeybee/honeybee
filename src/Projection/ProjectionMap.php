<?php

namespace Honeybee\Projection;

use Trellis\Runtime\Entity\EntityMap;

class ProjectionMap extends EntityMap
{
    protected function getItemImplementor()
    {
        return ProjectionInterface::CLASS;
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

<?php

namespace Honeybee\Projection;

use Trellis\Runtime\Entity\EntityList;

class ProjectionList extends EntityList
{
    protected function getItemImplementor()
    {
        return ProjectionInterface::CLASS;
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

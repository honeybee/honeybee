<?php

namespace Honeybee\Projection;

use Trellis\EntityType\Attribute\EntityList\EntityList;

class ProjectionList extends EntityList
{
    public function __construct(array $projection_types = [])
    {
        parent::__construct($projection_types, ProjectionInterface::CLASS);
    }

    /**
     * Convert the list to a map
     *
     * @return ProjectionMap
     */
    public function toMap()
    {
        $maped_items = [];
        /* @var \Honeybee\Projection\ProjectionInterface $item */
        foreach ($this->items as $item) {
            $maped_items[$item->getIdentifier()] = $item;
        }
        return new ProjectionMap($maped_items);
    }
}

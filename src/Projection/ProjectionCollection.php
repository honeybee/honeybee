<?php

namespace Honeybee\Projection;

use Trellis\Runtime\Entity\EntityList;

class ProjectionCollection extends EntityList
{
    protected function getItemImplementor()
    {
        return ProjectionInterface::CLASS;
    }

    /**
     * Returns whether the entities of the list have the same entity type.
     *
     * @return boolean true when list items have different entity types, false otherwise.
     */
    public function containsMultipleTypes()
    {
        $mixed = false;

        $types = [];
        foreach ($this->items as $resource) {
            $class = get_class($resource->getType());
            if (!in_array($class, $types, true)) {
                $types[] = $class;
            }
        }

        if (count($types) > 1) {
            $mixed = true;
        }

        return $mixed;
    }
}

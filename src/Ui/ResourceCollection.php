<?php

namespace Honeybee\Ui;

use Trellis\Collection\ItemList;

class ResourceCollection extends ItemList
{
    /**
     * Returns whether the items in the list have the same class
     *
     * @return boolean
     */
    public function containsMultipleTypes()
    {
        $mixed = false;

        $types = [];
        foreach ($this->items as $item) {
            $class = get_class($item);
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

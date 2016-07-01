<?php

namespace Honeybee\Ui;

use Trellis\Runtime\Entity\EntityList;
use Trellis\Common\Collection\CollectionInterface;

class ResourceCollection extends EntityList
{
    // CollectionInterface

    public function filter(Closure $callback)
    {
        $filtered_list = new static();

        foreach ($this->items as $item) {
            if ($callback($item) === true) {
                $filtered_list->push($item);
            }
        }

        return $filtered_list;
    }

    public function append(CollectionInterface $collection)
    {
        if (!$collection instanceof static) {
            throw new RuntimeException(
                sprintf("Can only append collections of the same type %s", get_class($this))
            );
        }

        foreach ($collection as $item) {
            $this->addItem($item);
        }
    }
}
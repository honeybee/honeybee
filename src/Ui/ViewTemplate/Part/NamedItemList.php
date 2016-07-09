<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Honeybee\Common\Error\RuntimeError;
use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class NamedItemList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $named_items = [])
    {
        parent::__construct(NamedItemInterface::CLASS, $named_items);
    }

    public function getByName($name)
    {
        foreach ($this->items as $item) {
            if ($item->getName() === $name) {
                return $item;
            }
        }

        throw new RuntimeError('Item with name not found: ' . $name);
    }
}

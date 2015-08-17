<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class CellList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return CellInterface::CLASS;
    }
}

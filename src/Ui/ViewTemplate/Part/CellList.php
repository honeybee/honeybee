<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class CellList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return CellInterface::CLASS;
    }
}

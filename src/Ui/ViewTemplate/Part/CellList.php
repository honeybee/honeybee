<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class CellList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $cells = [])
    {
        parent::__construct(CellInterface::CLASS, $cells);
    }
}

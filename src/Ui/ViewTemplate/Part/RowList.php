<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class RowList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $rows = [])
    {
        parent::__construct(RowInterface::CLASS, $rows);
    }
}

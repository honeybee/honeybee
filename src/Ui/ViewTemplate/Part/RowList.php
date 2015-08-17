<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class RowList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return RowInterface::CLASS;
    }
}

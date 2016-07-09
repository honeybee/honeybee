<?php

namespace Honeybee\Ui\ViewTemplate\Part;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class TabList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $tabs = [])
    {
        parent::__construct(TabInterface::CLASS, $tabs);
    }
}

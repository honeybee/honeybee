<?php

namespace Honeybee\Ui\ViewTemplate\Part;

class TabList extends NamedItemList
{
    protected function getItemImplementor()
    {
        return TabInterface::CLASS;
    }
}

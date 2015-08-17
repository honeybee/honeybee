<?php

namespace Honeybee\Ui\ViewTemplate\Part;

class PanelList extends NamedItemList
{
    protected function getItemImplementor()
    {
        return PanelInterface::CLASS;
    }
}

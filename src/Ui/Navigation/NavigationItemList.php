<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class NavigationItemList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return NavigationItemInterface::CLASS;
    }
}

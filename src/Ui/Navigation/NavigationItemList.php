<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class NavigationItemList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return NavigationItemInterface::CLASS;
    }
}

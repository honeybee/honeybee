<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class NavigationGroupMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return NavigationGroupInterface::CLASS;
    }
}

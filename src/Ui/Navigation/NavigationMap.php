<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class NavigationMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return NavigationInterface::CLASS;
    }
}

<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class NavigationGroupMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return NavigationGroupInterface::CLASS;
    }
}

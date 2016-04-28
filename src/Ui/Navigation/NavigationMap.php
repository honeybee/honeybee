<?php

namespace Honeybee\Ui\Navigation;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class NavigationMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return NavigationInterface::CLASS;
    }
}

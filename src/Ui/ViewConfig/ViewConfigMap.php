<?php

namespace Honeybee\Ui\ViewConfig;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class ViewConfigMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return ViewConfigInterface::CLASS;
    }
}

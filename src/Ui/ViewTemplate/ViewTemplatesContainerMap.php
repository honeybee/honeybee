<?php

namespace Honeybee\Ui\ViewTemplate;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class ViewTemplatesContainerMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return ViewTemplatesContainerInterface::CLASS;
    }
}

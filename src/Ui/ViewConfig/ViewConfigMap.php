<?php

namespace Honeybee\Ui\ViewConfig;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class ViewConfigMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return ViewConfigInterface::CLASS;
    }
}

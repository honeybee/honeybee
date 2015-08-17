<?php

namespace Honeybee\Ui\ViewTemplate;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class ViewTemplateMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return ViewTemplateInterface::CLASS;
    }
}

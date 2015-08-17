<?php

namespace Honeybee\Ui\Activity;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class ActivityContainerMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return ActivityContainerInterface::CLASS;
    }
}

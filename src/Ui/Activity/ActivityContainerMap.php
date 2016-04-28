<?php

namespace Honeybee\Ui\Activity;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class ActivityContainerMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return ActivityContainerInterface::CLASS;
    }
}

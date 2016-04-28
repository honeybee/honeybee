<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class FinderMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return FinderInterface::CLASS;
    }
}

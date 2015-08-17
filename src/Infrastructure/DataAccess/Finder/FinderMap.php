<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class FinderMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return FinderInterface::CLASS;
    }
}

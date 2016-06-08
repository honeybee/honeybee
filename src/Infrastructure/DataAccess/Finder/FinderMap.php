<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;

class FinderMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
{
    protected function getItemImplementor()
    {
        return FinderInterface::CLASS;
    }
}

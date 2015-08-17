<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class FixtureTargetMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return FixtureTargetInterface::CLASS;
    }
}

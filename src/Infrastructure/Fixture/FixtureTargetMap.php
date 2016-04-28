<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class FixtureTargetMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return FixtureTargetInterface::CLASS;
    }
}

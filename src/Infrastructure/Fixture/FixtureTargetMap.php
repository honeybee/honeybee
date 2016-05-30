<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class FixtureTargetMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
{
    protected function getItemImplementor()
    {
        return FixtureTargetInterface::CLASS;
    }
}

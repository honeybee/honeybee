<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class FixtureList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return FixtureInterface::CLASS;
    }
}

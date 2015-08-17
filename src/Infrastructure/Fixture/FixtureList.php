<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class FixtureList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return FixtureInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class FixtureList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $fixtures = [])
    {
        parent::__construct(FixtureInterface::CLASS, $fixtures);
    }
}

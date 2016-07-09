<?php

namespace Honeybee\Infrastructure\Fixture;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class FixtureTargetMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $fixture_targets = [])
    {
        parent::__construct(FixtureTargetInterface::CLASS, $fixture_targets);
    }
}

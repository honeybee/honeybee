<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class MigrationTargetMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return MigrationTargetInterface::CLASS;
    }
}

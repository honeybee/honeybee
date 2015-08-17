<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class MigrationTargetMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return MigrationTargetInterface::CLASS;
    }
}

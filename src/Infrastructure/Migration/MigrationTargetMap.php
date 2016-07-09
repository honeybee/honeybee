<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class MigrationTargetMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $migration_targets = [])
    {
        parent::__construct(MigrationTargetInterface::CLASS , $migration_targets);
    }
}

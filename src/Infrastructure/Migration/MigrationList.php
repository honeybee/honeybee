<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class MigrationList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $migrations = [])
    {
        parent::__construct(MigrationInterface::CLASS, $migrations);
    }
}

<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class MigrationList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return MigrationInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class MigrationList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return MigrationInterface::CLASS;
    }
}

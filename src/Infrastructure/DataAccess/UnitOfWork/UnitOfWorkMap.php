<?php

namespace Honeybee\Infrastructure\DataAccess\UnitOfWork;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class UnitOfWorkMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return UnitOfWorkInterface::CLASS;
    }
}

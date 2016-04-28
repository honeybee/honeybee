<?php

namespace Honeybee\Infrastructure\DataAccess\UnitOfWork;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class UnitOfWorkMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return UnitOfWorkInterface::CLASS;
    }
}

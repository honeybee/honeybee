<?php

namespace Honeybee\Infrastructure\DataAccess\UnitOfWork;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class UnitOfWorkMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $unit_of_works = [])
    {
        parent::__construct(UnitOfWorkInterface::CLASS, $unit_of_works);
    }
}

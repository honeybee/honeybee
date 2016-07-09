<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class FinderMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $finders = [])
    {
        parent::__construct(FinderInterface::CLASS, $finders);
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class StorageReaderMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return StorageReaderInterface::CLASS;
    }
}

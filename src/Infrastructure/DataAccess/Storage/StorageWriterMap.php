<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class StorageWriterMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return StorageWriterInterface::CLASS;
    }
}

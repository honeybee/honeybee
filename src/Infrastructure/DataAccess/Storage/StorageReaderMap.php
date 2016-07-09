<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class StorageReaderMap extends TypedMap implements UniqueItemInterface
{
    public function getByEntityType($entity_type_prefix)
    {
        $reader_name = sprintf('%s::projection.standard::view_store::reader', $entity_type_prefix);
        return $this->getItem($reader_name);
    }

    protected function getItemImplementor()
    {
        return StorageReaderInterface::CLASS;
    }
}

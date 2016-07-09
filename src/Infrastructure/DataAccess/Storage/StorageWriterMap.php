<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class StorageWriterMap extends TypedMap implements UniqueItemInterface
{
    public function getByEntityType($entity_type_prefix)
    {
        $writer_name = sprintf('%s::projection.standard::view_store::writer', $entity_type_prefix);
        return $this->getItem($writer_name);
    }

    protected function getItemImplementor()
    {
        return StorageWriterInterface::CLASS;
    }
}

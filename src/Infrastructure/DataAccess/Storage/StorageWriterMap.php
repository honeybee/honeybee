<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Honeybee\Common\Error\RuntimeError;
use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class StorageWriterMap extends TypedMap implements UniqueValueInterface
{
    public function getByEntityType($entity_type_prefix)
    {
        $writer_name = sprintf('%s::projection.standard::view_store::writer', $entity_type_prefix);
        if (!$this->hasKey($writer_name)) {
            throw new RuntimeError(sprintf('No storage-writer for key %s found.', $writer_name));
        }

        return $this->getItem($writer_name);
    }

    protected function getItemImplementor()
    {
        return StorageWriterInterface::CLASS;
    }
}

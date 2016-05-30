<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class StorageReaderMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
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

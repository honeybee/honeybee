<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class StorageWriterMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
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

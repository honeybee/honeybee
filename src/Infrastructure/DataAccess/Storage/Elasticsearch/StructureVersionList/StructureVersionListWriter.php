<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\StructureVersionList;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\ElasticsearchStorageWriter;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Migration\StructureVersionList;

class StructureVersionListWriter extends ElasticsearchStorageWriter
{
    public function write($structure_version_list, SettingsInterface $settings = null)
    {
        if (!$structure_version_list instanceof StructureVersionList) {
            throw new RuntimeError(
                sprintf('Invalid payload given to %s, expected type of %s', __METHOD__, StructureVersionList::CLASS)
            );
        }

        $this->writeData(
            $structure_version_list->getIdentifier(),
            [
                'identifier' => $structure_version_list->getIdentifier(),
                'versions' => $structure_version_list->toArray()
            ]
        );
    }
}

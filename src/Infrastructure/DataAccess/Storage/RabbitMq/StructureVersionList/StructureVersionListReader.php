<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\RabbitMq\StructureVersionList;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\RabbitMq\RabbitMqStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Infrastructure\Migration\StructureVersion;
use Honeybee\Infrastructure\Migration\StructureVersionList;

class StructureVersionListReader extends RabbitMqStorage implements StorageReaderInterface
{
    public function read($identifier, SettingsInterface $settings = null)
    {
        $bindings = $this->getExchangeBindings();

        $versions = [];
        foreach ($bindings as $version) {
            if ($version['routing_key'] === $identifier) {
                $versions[] = $version['arguments'];
            }
        }

        if (empty($versions)) {
            return null;
        }

        return $this->createStructureVersionList($identifier, $versions);
    }

    public function readAll(SettingsInterface $settings)
    {
        $bindings = $this->getExchangeBindings();

        $versions = [];
        foreach ($bindings as $version) {
            $versions[$version['routing_key']][] = $version['arguments'];
        }

        $data = [];
        foreach ($versions as $identifier => $version_list) {
            $data[] = $this->createStructureVersionList($identifier, $version_list);
        }

        return $data;
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createStructureVersionList($identifier, array $versions)
    {
        $structure_version_list = new StructureVersionList($identifier);

        // sort version list
        usort($versions, function ($a, $b) {
            return $a['version'] - $b['version'];
        });

        foreach ($versions as $version) {
            $structure_version_list->push(new StructureVersion($version));
        }

        return $structure_version_list;
    }
}

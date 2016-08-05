<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Memory\ProcessState;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Memory\ArrayStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;

class ProcessStateReader extends ArrayStorage implements StorageReaderInterface
{
    public function readAll(SettingsInterface $settings = null)
    {
        return $this->connector->getConnection()->getItems();
    }

    public function read($identifier, SettingsInterface $settings = null)
    {
        return $this->connector->getConnection()->getItem($identifier);
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }
}

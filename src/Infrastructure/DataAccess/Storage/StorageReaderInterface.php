<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Honeybee\Infrastructure\Config\SettingsInterface;
use IteratorAggregate;

interface StorageReaderInterface extends IteratorAggregate
{
    public function read($identifier, SettingsInterface $settings = null);

    public function readAll(SettingsInterface $settings);
}

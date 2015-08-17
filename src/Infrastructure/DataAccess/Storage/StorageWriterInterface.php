<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Honeybee\Infrastructure\Config\SettingsInterface;

interface StorageWriterInterface
{
    public function write($data, SettingsInterface $settings = null);

    public function delete($identifier, SettingsInterface $settings = null);
}

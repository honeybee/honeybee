<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Memory\ProcessState;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Memory\ArrayStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;

class ProcessStateWriter extends ArrayStorage implements StorageWriterInterface
{
    public function write($process_state, SettingsInterface $settings = null)
    {
        if (!$process_state instanceof ProcessStateInterface) {
            throw new RuntimeError(
                sprintf(
                    'Invalid payload given to %s, expected type of %s',
                    __METHOD__,
                    ProcessStateInterface::CLASS
                )
            );
        }

        $this->connector->getConnection()->setItem($process_state->getUuid(), $process_state);
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        $this->connector->getConnection()->offsetUnset($identifier);
    }
}

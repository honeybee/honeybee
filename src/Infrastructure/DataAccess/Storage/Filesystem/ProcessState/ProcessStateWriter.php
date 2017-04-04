<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Filesystem\ProcessState;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeygavi\ProcessManager\ProcessStateInterface;

class ProcessStateWriter extends Storage implements StorageWriterInterface
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

        $this->connector->getConnection()->put(
            $process_state->getUuid() . '.json',
            json_encode($process_state->toArray())
        );
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        $this->connector->getConnection()->delete($identifier . '.json');
    }
}

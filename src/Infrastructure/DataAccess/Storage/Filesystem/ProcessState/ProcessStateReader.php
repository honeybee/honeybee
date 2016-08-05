<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Filesystem\ProcessState;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;

class ProcessStateReader extends Storage implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $next_start_key = null;

    protected $identifier_list;

    public function readAll(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        if ($settings->get('first', true)) {
            $this->identifier_list = $this->fetchProcessStateIdentifiers();
        }
        $this->next_identifier = key($this->identifier_list);
        next($this->identifier_list);

        if (!$this->next_identifier) {
            return [];
        }

        return [ $this->read($this->next_identifier, $settings) ];
    }

    public function read($identifier, SettingsInterface $settings = null)
    {
        $file_system = $this->connector->getConnection();
        $path = $identifier . '.json';

        if ($file_system->has($path)) {
            $file_contents = $file_system->read($path);
            return $this->createProcessState(json_decode($file_contents, true));
        }

        return null;
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createProcessState(array $data)
    {
        $process_state_class = $data['@type'];
        if (!class_exists($process_state_class)) {
            throw new RuntimeError('Unable to load process-state class: ' . $process_state_class);
        }

        $process_state = new $process_state_class($data);
        if (!$process_state instanceof ProcessStateInterface) {
            throw new RuntimeError(
                sprintf(
                    'Given class "%s" does not implement required interface: %s',
                    $process_state_class,
                    ProcessStateInterface::CLASS
                )
            );
        }

        return $process_state;
    }

    protected function fetchProcessStateIdentifiers()
    {
        $process_state_identifiers = [];
        $directory = $this->connector->getConnection()->getPathPrefix();

        foreach (glob($directory . '/*.json') as $process_state_file) {
            $process_state_identifiers[] = str_replace('.json', '', basename($process_state_file));
        }

        return $process_state_identifiers;
    }
}

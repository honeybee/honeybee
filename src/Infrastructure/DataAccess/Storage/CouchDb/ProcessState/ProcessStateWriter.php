<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\ProcessState;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;

class ProcessStateWriter extends CouchDbStorage implements StorageWriterInterface
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

        $data = $process_state->toArray();
        $identifier = $process_state->getUuid();

        try {
            // @todo use head method to get current revision?
            $cur_data = $this->buildRequestFor($identifier, self::METHOD_GET)
                ->send()->json();
            $data['revision'] = $cur_data['_rev'];
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        try {
            $response_data = $this->buildRequestFor($identifier, self::METHOD_PUT, $data)
                ->send()->json();
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        if (!isset($response_data['ok']) || !isset($response_data['rev'])) {
            throw new RuntimeError("Failed to write process_state.");
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        try {
            // @todo use head method to get current revision?
            $cur_data = $this->buildRequestFor($identifier, self::METHOD_GET)
                ->send()->json();
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        $data = ['revision' => $cur_data['_rev'] ];
        try {
            $response_data = $this->buildRequestFor($identifier, self::METHOD_DELETE, $data)->send()->json();
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        if (!isset($response_data['ok'])) {
            throw new RuntimeError('Failed to delete process_state.');
        }
    }
}

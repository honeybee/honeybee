<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\SagaSubject;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Guzzle\Http\Exception\ClientErrorResponseException;

class SagaSubjectWriter extends CouchDbStorage implements StorageWriterInterface
{
    public function write($saga_subject, SettingsInterface $settings = null)
    {
        /*if (!$saga_subject instanceof SagaSubjectInterface) {
            throw new RuntimeError(
                sprintf(
                    'Invalid payload given to %s, expected type of %s',
                    __METHOD__,
                    SagaSubjectInterface::CLASS
                )
            );
        }*/

        $data = $saga_subject->toArray();
        $identifier = $saga_subject->getUuid();

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
            throw new RuntimeError("Failed to write saga.");
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
            throw new RuntimeError('Failed to delete saga.');
        }
    }
}

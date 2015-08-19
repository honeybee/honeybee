<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\SagaSubject;

use Guzzle\Http\Exception\BadResponseException;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;

class SagaSubjectReader extends CouchDbStorage implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $next_start_key = null;

    public function readAll(SettingsInterface $settings)
    {
        $data = [];

        $default_limit = $this->config->get('limit', self::READ_ALL_LIMIT);
        $request_params = [
            'include_docs' => 'true',
            'limit' => $settings->get('limit', $default_limit)
        ];

        if (!$settings->get('first', true)) {
            if (!$this->next_start_key) {
                return $data;
            }

            $request_params['startkey'] = sprintf('"%s"', $this->next_start_key);
            $request_params['skip'] = 1;
        }

        $result_data = $this->buildRequestFor(
            '_all_docs',
            self::METHOD_GET,
            [],
            $request_params
        )->send()->json();

        foreach ($result_data['rows'] as $row) {
            $data[] = $this->createSagaSubject($row);
        }

        if ($result_data['total_rows'] === $result_data['offset'] + 1) {
            $this->next_start_key = null;
        } else {
            $last_row = end($data);
            $this->next_start_key = $last_row[self::DOMAIN_FIELD_ID];
        }

        return $data;
    }

    public function read($identifier, SettingsInterface $settings = null)
    {
        try {
            $result_data = $this->buildRequestFor($identifier, self::METHOD_GET)->send()->json();
        } catch (BadResponseException $error) {
            if ($error->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                throw $error;
            }
        }

        $result_data['revision'] = $result_data['_rev'];

        return $this->createSagaSubject($result_data);
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createSagaSubject(array $data)
    {
        $saga_subject_class = $data['@type'];
        if (!class_exists($saga_subject_class)) {
            throw new RuntimeError('Unable to load saga class: ' . $saga_subject_class);
        }

        return new $saga_subject_class($data);
    }
}

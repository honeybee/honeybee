<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList;

use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\Migration\StructureVersionList;
use Honeybee\Infrastructure\Migration\StructureVersion;
use GuzzleHttp\Exception\RequestException;

class StructureVersionListReader extends CouchDbStorage implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $next_start_key = null;

    public function readAll(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

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

        $response = $this->request('_all_docs', self::METHOD_GET, [], $request_params);
        $result_data = json_decode($response->getBody(), true);

        foreach ($result_data['rows'] as $row) {
            $data[] = $this->createStructureVersionList($row);
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
            $response = $this->request($identifier, self::METHOD_GET);
            $result_data = json_decode($response->getBody(), true);
        } catch (RequestException $error) {
            if ($error->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                throw $error;
            }
        }

        $result_data['revision'] = $result_data['_rev'];

        return $this->createStructureVersionList($result_data);
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createStructureVersionList(array $data)
    {
        $structure_version_list = new StructureVersionList($data['_id']);

        foreach ($data['versions'] as $version_data) {
            $structure_version_list->push(new StructureVersion($version_data));
        }

        return $structure_version_list;
    }
}

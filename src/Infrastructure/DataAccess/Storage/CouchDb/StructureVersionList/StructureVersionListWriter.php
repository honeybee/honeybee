<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList;

use Assert\Assertion;
use GuzzleHttp\Exception\RequestException;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Migration\StructureVersionList;

class StructureVersionListWriter extends CouchDbStorage implements StorageWriterInterface
{
    public function write($structure_version_list, SettingsInterface $settings = null)
    {
        Assertion::isInstanceOf($structure_version_list, StructureVersionList::CLASS);

        $data = [
            'identifier' => $structure_version_list->getIdentifier(),
            'versions' => $structure_version_list->toArray()
        ];

        try {
            // @todo use head method to get current revision?
            $response = $this->request($data['identifier'], self::METHOD_GET);
            $structure_version = json_decode($response->getBody(), true);
            $data['revision'] = $structure_version['_rev'];
        } catch (RequestException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        try {
            $response = $this->request($data['identifier'], self::METHOD_PUT, $data);
            $response_data = json_decode($response->getBody(), true);
        } catch (RequestException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        if (!isset($response_data['ok']) || !isset($response_data['rev'])) {
            throw new RuntimeError('Failed to write data.');
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        try {
            $response = $this->request($identifier, self::METHOD_GET);
            $structure_version = json_decode($response->getBody(), true);
            $data['revision'] = $structure_version['_rev'];
            $this->request($identifier, self::METHOD_DELETE, [], $data);
        } catch (RequestException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }
    }
}

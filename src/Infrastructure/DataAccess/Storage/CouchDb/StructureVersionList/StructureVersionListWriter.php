<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Migration\StructureVersionList;
use GuzzleHttp\Exception\BadResponseException;

class StructureVersionListWriter extends CouchDbStorage implements StorageWriterInterface
{
    public function write($structure_version_list, SettingsInterface $settings = null)
    {
        if (!$structure_version_list instanceof StructureVersionList) {
            throw new RuntimeError(
                sprintf('Invalid payload given to %s, expected type of %s', __METHOD__, StructureVersionList::CLASS)
            );
        }

        $data = [
            'identifier' => $structure_version_list->getIdentifier(),
            'versions' => $structure_version_list->toArray()
        ];

        try {
            // @todo use head method to get current revision?
            $response = $this->buildRequestFor($data['identifier'], self::METHOD_GET)->send();
            $structure_version = json_decode($response->getBody(), true);
            $data['revision'] = $structure_version['_rev'];
        } catch (BadResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        try {
            $response = $this->buildRequestFor(
                $data['identifier'],
                self::METHOD_PUT,
                $data
            )->send();
            $response_data = json_decode($response->getBody(), true);
        } catch (BadResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        if (!isset($response_data['ok']) || !isset($response_data['rev'])) {
            throw new RuntimeError("Failed to write data.");
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        try {
            $response = $this->buildRequestFor($identifier, self::METHOD_GET)->send();
            $structure_version = json_decode($response->getBody(), true);
            $this->buildRequestFor(
                sprintf('%s?rev=%s', $identifier, $structure_version['_rev']),
                self::METHOD_DELETE
            )->send();
        } catch (BadResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }
    }
}

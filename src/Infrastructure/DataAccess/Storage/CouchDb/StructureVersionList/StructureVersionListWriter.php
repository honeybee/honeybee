<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\StructureVersionList;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\Migration\StructureVersionList;

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
            $structure_version = $this->buildRequestFor($data['identifier'], self::METHOD_GET)->send()->json();
            $data['revision'] = $structure_version['_rev'];
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        try {
            $response_data = $this->buildRequestFor(
                $data['identifier'],
                self::METHOD_PUT,
                $data
            )->send()->json();
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }

        if (!isset($response_data['ok']) || !isset($response_data['rev'])) {
            throw new RuntimeError("Failed to write data.");
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        try {
            $structure_version = $this->buildRequestFor($identifier, self::METHOD_GET)->send()->json();
            $this->buildRequestFor(
                sprintf('%s?rev=%s', $identifier, $structure_version['_rev']),
                self::METHOD_DELETE
            )->send();
        } catch (ClientErrorResponseException $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }
    }
}

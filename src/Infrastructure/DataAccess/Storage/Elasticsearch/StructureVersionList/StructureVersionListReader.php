<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\StructureVersionList;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\ElasticsearchStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Infrastructure\Migration\StructureVersion;
use Honeybee\Infrastructure\Migration\StructureVersionList;

class StructureVersionListReader extends ElasticsearchStorage implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $offset = 0;

    public function readAll(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        $data = [];

        $default_limit = $this->config->get('limit', self::READ_ALL_LIMIT);
        $query_params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'size' => $settings->get('limit', $default_limit),
            'body' => [ 'query' => [ 'match_all' => [] ] ]
        ];

        if (!$settings->get('first', true)) {
            if (!$this->offset) {
                return $data;
            }
            $query_params['from'] = $this->offset;
        }

        $raw_result = $this->connector->getConnection()->search($query_params);
        $result_hits = $raw_result['hits'];
        foreach ($result_hits['hits'] as $data_row) {
            $data[] = $this->createStructureVersionList($data_row['_source']);
        }

        if ($result_hits['total'] === $this->offset + 1) {
            $this->offset = 0;
        } else {
            // @fixme what limit?
            $this->offset += $limit;
        }

        return $data;
    }

    public function read($identifier, SettingsInterface $settings = null)
    {
        try {
            $result = $this->connector->getConnection()->get(
                [
                    'index' => $this->getIndex(),
                    'type' => $this->getType(),
                    'id' => $identifier
                ]
            );
        } catch (Missing404Exception $error) {
            return null;
        }

        return $this->createStructureVersionList($result['_source']);
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createStructureVersionList(array $data)
    {
        $structure_version_list = new StructureVersionList($data['identifier']);

        foreach ($data['versions'] as $version_data) {
            $structure_version_list->push(new StructureVersion($version_data));
        }

        return $structure_version_list;
    }
}

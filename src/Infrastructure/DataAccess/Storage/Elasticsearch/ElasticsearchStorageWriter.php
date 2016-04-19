<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch;

use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Honeybee\Infrastructure\DataAccess\Storage\IStorageKey;
use Elasticsearch\Common\Exceptions\Missing404Exception;

abstract class ElasticsearchStorageWriter extends ElasticsearchStorage implements StorageWriterInterface
{
    protected function writeData($identifier, array $data, SettingsInterface $settings = null)
    {
        $data = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $identifier,
            'body' => $data
        ];

        $this->connector->getConnection()->index(array_merge($data, $this->getParameters('index')));
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        try {
            // @todo atm deleting a document when the index does not exists triggers the index to be created.
            // this is a baad sideeffect caused by the elasticsearch-php lib atm.
            // remove this code as soon as the behaviour is fixed.
            // @link https://github.com/elastic/elasticsearch/issues/5809 This is an active ES issue

            $data = [
                'index' => $this->getIndex(),
                'type' => $this->getType(),
                'id' => $identifier
            ];

            // Parameters not added to the get because we don't want to force any refresh on read
            $this->connector->getConnection()->get(array_merge($data, $this->getParameters('get')));

            $this->connector->getConnection()->delete(array_merge($data, $this->getParameters('delete')));
        } catch (Missing404Exception $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }
    }
}

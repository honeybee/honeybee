<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;

abstract class ElasticsearchStorageWriter extends ElasticsearchStorage implements StorageWriterInterface
{
    protected function writeData($identifier, array $data, SettingsInterface $settings = null)
    {
        $data = [
            'index' => $this->getIndex(),
            'id' => $identifier,
            'body' => $data
        ];

        $index_per_type = $this->connector->getConfig()->get('index_per_type', false);
        if (!$index_per_type) {
            $data['type'] = $this->getType();
        }

        $this->connector->getConnection()->index(array_merge($data, $this->getParameters('index')));
    }

    protected function writeBulk(array $documents, SettingsInterface $settings = null)
    {
        // @todo support bulk parameters and batching
        $index = $this->getIndex();
        $type = $this->getType();

        $index_per_type = $this->connector->getConfig()->get('index_per_type', false);

        $data = [];
        foreach ($documents as $identifier => $document) {
            $meta = [
                'index' => [
                    '_index' => $index,
                    '_id' => $identifier
                ]
            ];
            if (!$index_per_type) {
                $meta['_type'] = $type;
            }

            $data['body'][] = $meta;
            $data['body'][] = $document;
        }

        $this->connector->getConnection()->bulk($data);
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        if (!is_string($identifier) || empty($identifier)) {
            $this->logger->debug(__METHOD__ . ' - Ignoring invalid identifier.');
            return;
        }

        try {
            $data = [
                'index' => $this->getIndex(),
                'id' => $identifier
            ];
            $index_per_type = $this->connector->getConfig()->get('index_per_type', false);
            if (!$index_per_type) {
                $data['type'] = $this->getType();
            }

            /*
             * @todo atm deleting a document when the index does not exists triggers the index to be created.
             * this is a baad sideeffect in Elasticsearch. remove this code as soon as the behaviour is fixed.
             * @link https://github.com/elastic/elasticsearch/issues/15451
             */

            // override potential refresh on read
            $connection = $this->connector->getConnection();
            $get_parameters = $this->getParameters('get');
            $get_parameters['refresh'] = false;
            $connection->get(array_merge($data, $get_parameters));

            $connection->delete(array_merge($data, $this->getParameters('delete')));
        } catch (Missing404Exception $error) {
            error_log(__METHOD__ . ' - ' . $error->getMessage());
        }
    }
}

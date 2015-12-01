<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\ElasticsearchStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Projection\ProjectionTypeInterface;
use Psr\Log\LoggerInterface;

class ProjectionReader extends ElasticsearchStorage implements StorageReaderInterface
{
    const READ_ALL_LIMIT = 10;

    protected $offset = 0;

    public function __construct(
        ConnectorInterface $connector,
        ConfigInterface $config,
        LoggerInterface $logger,
        ProjectionTypeInterface $resource_type
    ) {
        parent::__construct($connector, $config, $logger);

        $this->resource_type = $resource_type;
    }

    public function readAll(SettingsInterface $settings)
    {
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

$time = microtime(true);
        $raw_result = $this->connector->getConnection()->search($query_params);
$now = microtime(true);
        $result_hits = $raw_result['hits'];
error_log('Elasticsearch ProjectionReader::readAll SEARCH: ' . $result_hits['total'] . ' hits took ' . round(($now - $time) * 1000, 1) . 'ms');
        foreach ($result_hits['hits'] as $data_row) {
            $data[] = $this->resource_type->createEntity($data_row['_source']);
        }

        if ($result_hits['total'] === $this->offset + 1) {
            $this->offset = 0;
        } else {
            $this->offset += $limit;
        }

        return $data;
    }

    public function read($identifier, SettingsInterface $settings = null)
    {
        try {
$time = microtime(true);
            $result = $this->connector->getConnection()->get(
                [
                    'index' => $this->getIndex(),
                    'type' => $this->getType(),
                    'id' => $identifier
                ]
            );
$now = microtime(true);
error_log('Elasticsearch ProjectionReader::read GET ' . $identifier . ': ' . round(($now - $time) * 1000, 1) . 'ms');
        } catch (Missing404Exception $missing_error) {
var_dump("MISS: " . $this->getIndex() . ', type: ' . $this->getType() . ', id: ' . $identifier . ', msg: ' . $missing_error->getMessage());
            return null;
        }

$time = microtime(true);
        $entity = $this->resource_type->createEntity($result['_source']);
$now = microtime(true);
error_log('Elasticsearch ProjectionReader::read createEntity ' . $identifier . ': ' . round(($now - $time) * 1000, 1) . 'ms');
        return $entity;
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function getType()
    {
        return $this->config->get('type', $this->resource_type->getPrefix());
    }
}

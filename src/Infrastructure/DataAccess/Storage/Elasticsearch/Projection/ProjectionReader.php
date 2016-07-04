<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\Projection;

use Elasticsearch\Common\Exceptions\Missing404Exception;
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
        ProjectionTypeInterface $projection_type
    ) {
        parent::__construct($connector, $config, $logger);

        $this->projection_type = $projection_type;
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

        $raw_result = $this->connector->getConnection()->search($query_params);
        $result_hits = $raw_result['hits'];
        foreach ($result_hits['hits'] as $data_row) {
            $data[] = $this->projection_type->createEntity($data_row['_source']);
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
            $result = $this->connector->getConnection()->get(
                [
                    'index' => $this->getIndex(),
                    'type' => $this->getType(),
                    'id' => $identifier
                ]
            );
        } catch (Missing404Exception $missing_error) {
            var_dump(
                "MISS: " .
                $this->getIndex() .
                ', type: ' .
                $this->getType() .
                ', id: ' .
                $identifier .
                ', msg: ' .
                $missing_error->getMessage()
            );
            return null;
        }

        return $this->projection_type->createEntity($result['_source']);
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function getType()
    {
        return $this->config->get('type', $this->projection_type->getPrefix());
    }
}

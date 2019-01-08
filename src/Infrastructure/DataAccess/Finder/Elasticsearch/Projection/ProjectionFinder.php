<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\Projection;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\ElasticsearchFinder;
use Honeybee\Projection\ProjectionTypeMap;
use Psr\Log\LoggerInterface;

class ProjectionFinder extends ElasticsearchFinder
{
    protected $projection_type_map;

    public function __construct(
        ConnectorInterface $connector,
        ConfigInterface $config,
        LoggerInterface $logger,
        ProjectionTypeMap $projection_type_map
    ) {
        parent::__construct($connector, $config, $logger);

        $this->projection_type_map = $projection_type_map;
    }

    private function createResult(array $document_data)
    {
        $source = $document_data['_source'];
        $event_type = isset($source[self::OBJECT_TYPE]) ? $source[self::OBJECT_TYPE] : false;
        if (!$event_type) {
            throw new RuntimeError(
                'Invalid or corrupt type information within projection data for _id: ' . @$document_data['_id'] ?: ''
            );
        }
        unset($source[self::OBJECT_TYPE]);

        return $this->projection_type_map->getItem($event_type)->createEntity($source);
    }

    protected function mapResultData(array $result_data)
    {
        if ($this->config->get('log_result_data', false) === true) {
            $this->logger->debug('['.__METHOD__.'] raw result = ' . json_encode($result_data, JSON_PRETTY_PRINT));
        }

        $results = [];

        if (isset($result_data['_source'])) {
            // Handling for single document
            $results[] = $this->createResult($result_data);
        } elseif (isset($result_data['hits'])) {
            // Handling for search results
            $hits = $result_data['hits'];
            foreach ($hits['hits'] as $hit) {
                $results[] = $this->createResult($hit);
            }
        } elseif (isset($result_data['docs'])) {
            // Handling for multi-get documents
            $docs = $result_data['docs'];
            foreach ($docs as $doc) {
                if (true === $doc['found']) {
                    $results[] = $this->createResult($doc);
                }
            }
        }

        return $results;
    }
}

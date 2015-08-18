<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\Projection;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Finder\ElasticSearch\ElasticSearchFinder;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Projection\ProjectionTypeMap;
use Psr\Log\LoggerInterface;

class MixedProjectionFinder extends ElasticSearchFinder
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

    /**
     * Retrieves a document via GET for the configured index across ALL types.
     *
     * @return FinderResult result with one or no actual results
     */
    public function getByIdentifier($identifier)
    {
        $data = [
            'index' => $this->getIndex(),
            'type' => '_all',
            'id' => $identifier
        ];

        $query = array_merge($data, $this->getParameters('get'));

        if ($this->config->get('log_get_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] get query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->get($query);

        $mapped_results = $this->mapResultData($raw_result);

        return new FinderResult($mapped_results, count($mapped_results));
    }

    /**
     * Retrieves documents via MGET for the configured index across ALL types.
     *
     * @return FinderResult result with zero or more actual results
     */
    public function getByIdentifiers(array $identifiers)
    {
        $data = [
            'index' => $this->getIndex(),
            'type' => '_all',
            'body' => [
                'ids' => $identifiers
            ]
        ];

        $query = array_merge($data, $this->getParameters('mget'));

        if ($this->config->get('log_mget_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] mget query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->mget($query);

        $mapped_results = $this->mapResultData($raw_result);

        return new FinderResult($mapped_results, count($mapped_results));
    }

    /**
     * Retrieves documents via SEARCH for the configured index and type.
     *
     * @return FinderResult result with zero or more actual results
     */
    public function find(array $query)
    {
        $query['index'] = $this->getIndex();
        $query['type'] = $this->getType();

        if ($this->config->get('log_search_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] Search query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->search($query);

        return new FinderResult(
            $this->mapResultData($raw_result),
            $raw_result['hits']['total'],
            $query['from'] ?: 0
        );
    }

    protected function mapResultData(array $result_data)
    {
        if ($this->config->get('log_result_data', false) === true) {
            $this->logger->debug('['.__METHOD__.'] Raw result = ' . json_encode($result_data, JSON_PRETTY_PRINT));
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

    private function createResult(array $document_data)
    {
        $source = $document_data['_source'];

        $fqcn = isset($source[self::OBJECT_TYPE]) ? $source[self::OBJECT_TYPE] : false;
        if (!$fqcn || !class_exists($fqcn, true)) {
            throw new RuntimeError(
                'Invalid or corrupt type information within document data. "_source[@type]" given is: ' . $fqcn
            );
        }
        unset($source[self::OBJECT_TYPE]);

        $resource_type = $this->projection_type_map->getByEntityImplementor($fqcn);

        return $resource_type->createEntity($source);
    }
}
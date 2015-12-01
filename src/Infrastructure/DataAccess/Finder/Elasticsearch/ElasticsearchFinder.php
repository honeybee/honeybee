<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Honeybee\Infrastructure\DataAccess\Finder\Finder;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Infrastructure\Config\Settings;
use Elasticsearch\Common\Exceptions\Missing404Exception;

abstract class ElasticsearchFinder extends Finder
{
    abstract protected function mapResultData(array $result_data);

    public function getByIdentifier($identifier)
    {
        $data = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'id' => $identifier
        ];

        $query = array_merge($data, $this->getParameters('get'));

        if ($this->config->get('log_get_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] get query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        try {
            if ($this->config->get('log_connection_time', false) === true) {
                $time = microtime(true);
            }
            $raw_result = $this->connector->getConnection()->get($query);
            if ($this->config->get('log_connection_time', false) === true) {
                $now = microtime(true);
                $this->logger->info('['.__METHOD__.'] get ' . $identifier . ': ' . round(($now - $time) * 1000, 2) . 'ms');
            }

            if ($this->config->get('log_result_mapping_time', false) === true) {
                $time = microtime(true);
            }
            $mapped_results = $this->mapResultData($raw_result);
            if ($this->config->get('log_result_mapping_time', false) === true) {
                $now = microtime(true);
                $this->logger->info('['.__METHOD__.'] get – mapping of ' . count($mapped_results) . ' results: ' . round(($now - $time) * 1000, 2) . 'ms');
            }
        } catch (Missing404Exception $error) {
            $mapped_results = [];
        }

        return new FinderResult($mapped_results, count($mapped_results));
    }

    public function getByIdentifiers(array $identifiers)
    {
        $data = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'ids' => $identifiers
            ]
        ];

        $query = array_merge($data, $this->getParameters('mget'));

        if ($this->config->get('log_mget_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] mget query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        if ($this->config->get('log_connection_time', false) === true) {
            $time = microtime(true);
        }
        $raw_result = $this->connector->getConnection()->mget($query);
        if ($this->config->get('log_connection_time', false) === true) {
            $now = microtime(true);
            $this->logger->info('['.__METHOD__.'] mget ' . count($identifiers) . ' ids: ' . round(($now - $time) * 1000, 2) . 'ms');
        }

        if ($this->config->get('log_result_mapping_time', false) === true) {
            $time = microtime(true);
        }
        $mapped_results = $this->mapResultData($raw_result);
        if ($this->config->get('log_result_mapping_time', false) === true) {
            $now = microtime(true);
            $this->logger->info('['.__METHOD__.'] mget – mapping of ' . count($mapped_results) . ' results: ' . round(($now - $time) * 1000, 2) . 'ms');
        }

        return new FinderResult($mapped_results, count($mapped_results));
    }

    public function find(array $query)
    {
        $query['index'] = $this->getIndex();
        $query['type'] = $this->getType();

        if ($this->config->get('log_search_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] search query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        if ($this->config->get('log_connection_time', false) === true) {
            $time = microtime(true);
        }
        $raw_result = $this->connector->getConnection()->search($query);
        if ($this->config->get('log_connection_time', false) === true) {
            $now = microtime(true);
            $this->logger->info('['.__METHOD__.'] search: ' . round(($now - $time) * 1000, 2) . 'ms');
        }

        if ($this->config->get('log_result_mapping_time', false) === true) {
            $time = microtime(true);
        }
        $mapped_results = $this->mapResultData($raw_result);
        if ($this->config->get('log_result_mapping_time', false) === true) {
            $now = microtime(true);
            $this->logger->info('['.__METHOD__.'] search – mapping of ' . count($mapped_results) . ' results: ' . round(($now - $time) * 1000, 2) . 'ms');
        }

        return new FinderResult(
            $mapped_results,
            $raw_result['hits']['total'],
            $query['from'] ?: 0
        );
    }

    protected function getIndex()
    {
        $fallback_index = $this->connector->getConfig()->get('index');

        if (is_array($fallback_index)) {
            $fallback_index = array_values($fallback_index);
        }

        return $this->config->get('index', $fallback_index);
    }

    protected function getType()
    {
        return $this->config->get('type');
    }

    protected function getParameters($method)
    {
        return (array)$this->config->get('parameters', new Settings())->get($method);
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Assert\Assertion;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Finder\Finder;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;

abstract class ElasticsearchFinder extends Finder
{
    abstract protected function mapResultData(array $result_data);

    public function getByIdentifier($identifier)
    {
        Assertion::string($identifier);
        Assertion::notBlank($identifier);

        $index = $this->getIndex();

        if (empty($index) || $index == '_all' ||
            (is_array($index) && count($index) > 1) ||
            (is_string($index) && strpos($index, ',') !== false)
        ) {
            throw new RuntimeError(
                sprintf(
                    'Elasticsearch single index APIs do not support multiple indices: %s',
                    var_export($index, true)
                )
            );
        }

        // @todo multiple type search is not supported unless _all

        $data = [
            'index' => $index,
            'type' => $this->getType(),
            'id' => $identifier
        ];

        $query = array_merge($data, $this->getParameters('get'));

        if ($this->config->get('log_get_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] get query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        try {
            $raw_result = $this->connector->getConnection()->get($query);
            $mapped_results = $this->mapResultData($raw_result);
        } catch (Missing404Exception $error) {
            $mapped_results = [];
        }

        return new FinderResult($mapped_results, count($mapped_results));
    }

    public function getByIdentifiers(array $identifiers)
    {
        Assertion::notEmpty($identifiers);

        $index = $this->getIndex();

        if (empty($index) || $index === '_all' ||
            (is_array($index) && count($index) > 1) ||
            (is_string($index) && strpos($index, ',') !== false)
        ) {
            throw new RuntimeError(
                sprintf(
                    'Elasticsearch single index APIs do not support multiple indices: %s',
                    var_export($index, true)
                )
            );
        }

        $data = [
            'index' => $index,
            'body' => [
                'ids' => $identifiers,
                'size' => 100000
            ]
        ];
        if ($type = $this->getType()) {
            $data['type'] = $type;
        }

        $query = array_merge($data, $this->getParameters('mget'));

        if ($this->config->get('log_mget_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] mget query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->mget($query);

        $mapped_results = $this->mapResultData($raw_result);

        return new FinderResult($mapped_results, count($mapped_results));
    }

    public function find($query)
    {
        Assertion::isArray($query);

        $query['index'] = $this->getIndex();
        $type = $this->getType();
        if ($type && $type !== '_all') {
            $query['type'] = $type;
        }

        if ($this->config->get('log_search_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] search query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->search($query);

        return new FinderResult(
            $this->mapResultData($raw_result),
            $raw_result['hits']['total'],
            isset($query['from']) ? $query['from'] : 0
        );
    }

    public function findByStored($query)
    {
        Assertion::isArray($query);

        $query['index'] = $this->getIndex();
        $query['type'] = $this->getType();

        if ($this->config->get('log_search_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] stored query = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->searchTemplate($query);

        return new FinderResult(
            $this->mapResultData($raw_result),
            $raw_result['hits']['total'],
            isset($query['body']['params']['from']) ? $query['body']['params']['from'] : 0
        );
    }

    public function scrollStart($query, $cursor = null)
    {
        Assertion::isArray($query);

        $query['index'] = $this->getIndex();
        $query['type'] = $this->getType();
        $query['scroll'] = $this->config->get('scroll_timeout', '1m');
        $query['sort'] = [ '_doc' ];

        if ($this->config->get('log_scroll_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] scroll start = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        // Elasticsearch returns results on scroll start
        $raw_result = $this->connector->getConnection()->search($query);

        if (!isset($raw_result['_scroll_id'])) {
            $this->logger->error('Elasticsearch scrolling search request did not return a _scroll_id.');
        }

        return new FinderResult(
            $this->mapResultData($raw_result),
            $raw_result['hits']['total'],
            0, // unknown offset during scroll
            $raw_result['_scroll_id']
        );
    }

    public function scrollNext($cursor, $size = null)
    {
        Assertion::notEmpty($cursor);

        $query['scroll_id'] = $cursor;
        $query['scroll'] = $this->config->get('scroll_timeout', '1m');

        if ($this->config->get('log_scroll_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] scroll next = ' . json_encode($query, JSON_PRETTY_PRINT));
        }

        $raw_result = $this->connector->getConnection()->scroll($query);

        if (!isset($raw_result['_scroll_id'])) {
            $this->logger->error('Elasticsearch scrolling search request did not return a _scroll_id.');
        }

        return new FinderResult(
            $this->mapResultData($raw_result),
            $raw_result['hits']['total'],
            0, // unknown offset during scroll
            $raw_result['_scroll_id']
        );
    }

    public function scrollEnd($cursor)
    {
        Assertion::notEmpty($cursor);

        if ($this->config->get('log_scroll_query', false) === true) {
            $this->logger->debug('['.__METHOD__.'] scroll end ' . $cursor);
        }

        $this->connector->getConnection()->clearScroll([ 'scroll_id' => $cursor ]);
    }

    protected function getIndex()
    {
        $fallback_index = $this->connector->getConfig()->get('index', '_all');

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
        return (array)$this->config->get('parameters', new Settings)->get($method);
    }
}

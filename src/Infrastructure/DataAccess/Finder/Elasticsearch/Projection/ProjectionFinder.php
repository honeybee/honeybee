<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\Projection;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\ElasticsearchFinder;
use Honeybee\Projection\ProjectionTypeInterface;
use Psr\Log\LoggerInterface;

class ProjectionFinder extends ElasticsearchFinder
{
    public function __construct(
        ConnectorInterface $connector,
        ConfigInterface $config,
        LoggerInterface $logger,
        ProjectionTypeInterface $projection_type
    ) {
        parent::__construct($connector, $config, $logger);

        $this->projection_type = $projection_type;
    }

    private function createResult(array $document_data)
    {
        $source = $document_data['_source'];
        $event_type = isset($source[self::OBJECT_TYPE]) ? $source[self::OBJECT_TYPE] : false;
        if (!$event_type) {
            throw new RuntimeError('Invalid or corrupt type information within projection data.');
        }
        unset($source[self::OBJECT_TYPE]);

        if ($this->projection_type->getVariantPrefix() !== $event_type) {
            throw new RuntimeError(sprintf(
                'Unexpected type "%s" within projection data. Expecting "%s".',
                $event_type,
                $this->projection_type->getVariantPrefix()
            ));
        }

        return $this->projection_type->createEntity($source);
    }

    protected function mapResultData(array $result_data)
    {
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

    protected function getType()
    {
        return $this->config->get('type', $this->projection_type->getPrefix());
    }
}

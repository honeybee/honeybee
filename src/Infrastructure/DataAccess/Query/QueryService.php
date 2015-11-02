<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Projection\ProjectionTypeInterface;
use Psr\Log\LoggerInterface;

class QueryService implements QueryServiceInterface
{
    protected $config;

    protected $logger;

    protected $finder_mappings;

    public function __construct(
        ConfigInterface $config,
        array $finder_mappings,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->finder_mappings = $finder_mappings;
        $this->logger = $logger;

        if (!$this->config->has('default_mapping')) {
            throw new RuntimeError('Missing required default_mapping setting.');
        }
    }

    public function findByIdentifier($identifier)
    {
        return $this->getFinder()->getByIdentifier($identifier);
    }

    public function findByIdentifiers(array $identifiers)
    {
        return $this->getFinder()->getByIdentifiers($identifiers);
    }

    public function walkResources(QueryInterface $query, Closure $callback)
    {
        $query_result = $this->find($query);
        $resources = $query_result->getResults();

        while (count($resources) > 0) {
            foreach ($resources as $resource) {
                $callback($resource);
            }
            $query = $query->createCopyWith([ 'offset' => $query->getOffset() + $query->getLimit() ]);
            $query_result = $this->find($query);
            $results = $query_result->getResults();
        }
    }

    public function find(QueryInterface $query)
    {
        return $this->getFinder()->find(
            $this->getQueryTranslation()->translate($query)
        );
    }

    public function findEventsByIdentifier($identifier, $offset = 0, $limit = 10000)
    {
        return $this->getFinder('domain_event')->find(
            $this->getQueryTranslation('domain_event')->translate(
                new Query(
                    new CriteriaList,
                    new CriteriaList([ new AttributeCriteria('aggregate_root_identifier', $identifier) ]),
                    new CriteriaList([ new SortCriteria('seq_number', SortCriteria::SORT_ASC) ]),
                    $offset,
                    $limit
                )
            )
        );
    }

    protected function getFinder($finder_mapping_name = null)
    {
        $finder_mapping_name = $finder_mapping_name ?: $this->config->get('default_mapping');
        if (!isset($this->finder_mappings[$finder_mapping_name])) {
            throw new RuntimeError('No finder mapping configured for key: ' . $finder_mapping_name);
        }
        return $this->finder_mappings[$finder_mapping_name]['finder'];
    }

    protected function getQueryTranslation($finder_mapping_name = null)
    {
        $finder_mapping_name = $finder_mapping_name ?: $this->config->get('default_mapping');
        if (!isset($this->finder_mappings[$finder_mapping_name])) {
            throw new RuntimeError('No finder mapping configured for key: ' . $finder_mapping_name);
        }
        return $this->finder_mappings[$finder_mapping_name]['query_translation'];
    }
}

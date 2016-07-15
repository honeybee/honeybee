<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Closure;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;

class ProjectionQueryService extends QueryService implements ProjectionQueryServiceInterface
{
    public function findByIdentifier($identifier, $mapping_name = null)
    {
        return $this->getFinder($mapping_name)->getByIdentifier($identifier);
    }

    public function findByIdentifiers(array $identifiers, $mapping_name = null)
    {
        return $this->getFinder($mapping_name)->getByIdentifiers($identifiers);
    }

    public function walkResources(QueryInterface $query, Closure $callback, $mapping_name = null)
    {
        $query_result = $this->find($query, $mapping_name);
        $offset = $query_result->getOffset();
        $resources = $query_result->getResults();

        // @todo scan and scroll support
        while (count($resources) > 0) {
            foreach ($resources as $resource) {
                $callback($resource, $offset++, $query_result->getTotalCount());
            }
            $query = $query->createCopyWith([ 'offset' => $query->getOffset() + $query->getLimit() ]);
            $query_result = $this->find($query, $mapping_name);
            $offset = $query->getOffset();
            $resources = $query_result->getResults();
        }
    }

    public function find(QueryInterface $query, $mapping_name = null)
    {
        return $this->getFinder($mapping_name)->find(
            $this->getQueryTranslation()->translate($query)
        );
    }
}

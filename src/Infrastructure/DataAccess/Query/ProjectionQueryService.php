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

    public function walk(QueryInterface $query, Closure $callback, $mapping_name = null)
    {
        $query_result = $this->find($query, $mapping_name);
        $offset = $query_result->getOffset();

        while ($query_result->getCount() > 0) {
            $projections = $query_result->getResults();
            foreach ($projections as $projection) {
                $callback($projection, $offset++, $query_result->getTotalCount());
            }
            $query = $query->createCopyWith([ 'offset' => $query->getOffset() + $query->getLimit() ]);
            $query_result = $this->find($query, $mapping_name);
            $offset = $query->getOffset();
            $projections = $query_result->getResults();
        }
    }

    public function scroll(QueryInterface $query, Closure $callback, $mapping_name = null, $cursor = null)
    {
        $finder = $this->getFinder($mapping_name);
        $query_translation = $this->getQueryTranslation($mapping_name)->translate($query);
        $query_result = $finder->scrollStart($query_translation, $cursor);
        $offset = 0;

        while ($query_result->hasResults()) {
            foreach ($query_result->getResults() as $projection) {
                $callback($projection, $offset++, $query_result->getTotalCount());
            }
            $query_result = $finder->scrollNext($query_result->getCursor(), $query->getLimit());
        }

        $finder->scrollEnd($query_result->getCursor());
    }

    public function find(QueryInterface $query, $mapping_name = null)
    {
        // default custom query mapping name
        if (empty($mapping_name) && $query instanceof CustomQueryInterface) {
            $mapping_name = 'custom';
        }

        $finder = $this->getFinder($mapping_name);
        $query_translation = $this->getQueryTranslation($mapping_name)->translate($query);

        if ($query instanceof StoredQueryInterface) {
            $finder_result = $finder->findByStored($query_translation);
        } else {
            $finder_result = $finder->find($query_translation);
        }

        return $finder_result;
    }
}

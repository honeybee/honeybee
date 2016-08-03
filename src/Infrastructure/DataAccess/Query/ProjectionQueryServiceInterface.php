<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Closure;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;

interface ProjectionQueryServiceInterface extends QueryServiceInterface
{
    public function findByIdentifier($identifier, $mapping_name = null);

    public function findByIdentifiers(array $identifiers, $mapping_name = null);

    public function walk(QueryInterface $query, Closure $callback, $mapping_name = null);

    public function scroll(QueryInterface $query, Closure $callback, $mapping_name = null, $cursor = null);

    public function find(QueryInterface $query, $mapping_name = null);
}

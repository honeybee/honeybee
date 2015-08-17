<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Closure;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;

interface QueryServiceInterface
{
    public function findByIdentifier($identifier);

    public function findByIdentifiers(array $identifiers);

    public function walkResources(QueryInterface $query, Closure $callback);

    public function find(QueryInterface $query);
}

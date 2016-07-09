<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Projection\ProjectionTypeInterface;
use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class QueryServiceMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $query_services = [])
    {
        parent::__construct(QueryServiceInterface::CLASS, $query_services);
    }

    public function getByProjectionType(ProjectionTypeInterface $projection_type)
    {
        $query_service_key = sprintf('%s::query_service', $projection_type->getPrefix());
        return $this->getItem($query_service_key);
    }
}

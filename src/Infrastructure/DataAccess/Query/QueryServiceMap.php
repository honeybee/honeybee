<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Projection\ProjectionTypeInterface;
use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class QueryServiceMap extends TypedMap implements UniqueCollectionInterface
{
    public function getByProjectionType(ProjectionTypeInterface $projection_type)
    {
        $query_service_key = sprintf('%s::query_service', $projection_type->getPrefix());

        if (!$this->hasKey($query_service_key)) {
            throw new RuntimeError('No query_service found for given given key: ' . $query_service_key);
        }

        return $this->getItem($query_service_key);
    }

    protected function getItemImplementor()
    {
        return QueryServiceInterface::CLASS;
    }
}

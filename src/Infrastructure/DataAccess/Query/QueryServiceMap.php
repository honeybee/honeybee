<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

use Honeybee\Projection\ProjectionTypeInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;

class QueryServiceMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
{
    public function getByProjectionType(ProjectionTypeInterface $projection_type)
    {
        $query_service_key = sprintf('%s::query_service', $projection_type->getVariantPrefix());
        return $this->getItem($query_service_key);
    }

    protected function getItemImplementor()
    {
        return QueryServiceInterface::CLASS;
    }
}

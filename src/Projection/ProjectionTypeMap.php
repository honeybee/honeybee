<?php

namespace Honeybee\Projection;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Runtime\EntityTypeMap;

class ProjectionTypeMap extends EntityTypeMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return ProjectionTypeInterface::CLASS;
    }

    public function filterByPrefix($prefix)
    {
        return $this->filter(
            function (ProjectionType $type) use ($prefix) {
                return strpos($type->getPrefix(), $prefix) === 0;
            }
        );
    }

    public function getByAggregateRootType(
        AggregateRootTypeInterface $aggregate_root_type,
        $variant = ProjectionTypeInterface::DEFAULT_VARIANT
    ) {
        return $this->getItem(
            sprintf(
                '%s::projection.%s',
                $aggregate_root_type->getPrefix(),
                StringToolkit::asSnakeCase($variant)
            )
        );
    }
}

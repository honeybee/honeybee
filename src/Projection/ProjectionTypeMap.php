<?php

namespace Honeybee\Projection;

use Trellis\EntityType\EntityTypeMap;

class ProjectionTypeMap extends EntityTypeMap
{
    /**
     * @param ProjectionTypeInterface[] $projection_types
     */
    public function __construct(array $projection_types = [])
    {
        parent::__construct($projection_types, ProjectionTypeInterface::CLASS);
    }

    public function filterByVendorPackage($vendor, $package)
    {
        return $this->filter(
            function (ProjectionType $type) use ($vendor, $package) {
                return ($type->getVendor() === $vendor && $type->getPackage() === $package);
            }
        );
    }
}

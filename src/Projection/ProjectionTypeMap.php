<?php

namespace Honeybee\Projection;

use Trellis\Runtime\EntityTypeMap;
use Trellis\Common\Collection\MandatoryKeyInterface;

class ProjectionTypeMap extends EntityTypeMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return ProjectionTypeInterface::CLASS;
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

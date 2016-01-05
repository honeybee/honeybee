<?php

namespace Honeybee\Projection;

use Trellis\Common\Collection\TypedMap;
use Honeybee\Common\Error\RuntimeError;

class ProjectionTypeMap extends TypedMap
{
    public function getItemImplementor()
    {
        return ProjectionTypeInterface::CLASS;
    }

    public function getByClassName($fqcn)
    {
        // TODO why do we have leading backspaces?
        $fqcn = trim($fqcn, "\\");
        $matched_types = $this->filter(
            function ($doc_type) use ($fqcn) {
                return get_class($doc_type) === $fqcn;
            }
        );

        if ($matched_types->getSize() !== 1) {
            throw new RuntimeError(
                sprintf('Unexpected number of matching types for %s call and given classname: %s', __METHOD__, $fqcn)
            );
        }

        $types_arr = $matched_types->getValues();

        return $types_arr[0];
    }

    public function getByEntityImplementor($fqcn)
    {
        $matched_types = $this->filter(
            function ($doc_type) use ($fqcn) {
                $impl = $doc_type::getEntityImplementor();
                // TODO why do we have leading backspaces?
                $impl = ltrim($impl, "\\");
                return $impl === $fqcn;
            }
        );

        if ($matched_types->getSize() !== 1) {
            throw new RuntimeError(
                sprintf('Unexpected number of matching types for %s call and given classname: %s', __METHOD__, $fqcn)
            );
        }

        $types_arr = $matched_types->getValues();

        return $types_arr[0];
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

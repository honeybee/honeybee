<?php

namespace Honeybee\Model\Aggregate;

use Honeybee\Common\Error\RuntimeError;
use Trellis\Common\Collection\TypedMap;

class AggregateRootTypeMap extends TypedMap
{
    public function getItemImplementor()
    {
        return AggregateRootType::CLASS;
    }

    public function getByClassName($fqcn)
    {
        $fqcn = trim($fqcn, "\\");
        $matched_types = $this->filter(
            function ($doc_type) use ($fqcn) {
                return get_class($doc_type) === $fqcn;
            }
        );

        if ($matched_types->getSize() !== 1) {
            throw new RuntimeError(
                sprintf('Unexpected number of matching types for %s call and given class: %s', __METHOD__, $fqcn)
            );
        }

        $types_arr = $matched_types->getValues();

        return $types_arr[0];
    }
}

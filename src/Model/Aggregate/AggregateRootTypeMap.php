<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\EntityTypeMap;
use Trellis\Common\Collection\MandatoryKeyInterface;

class AggregateRootTypeMap extends EntityTypeMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return AggregateRootType::CLASS;
    }
}

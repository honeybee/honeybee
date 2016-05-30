<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class MigrationTargetMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
{
    protected function getItemImplementor()
    {
        return MigrationTargetInterface::CLASS;
    }
}

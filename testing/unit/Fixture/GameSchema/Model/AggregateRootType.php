<?php

namespace Honeybee\Tests\Fixture\GameSchema\Model;

use Honeybee\Model\Aggregate\AggregateRootType as BaseAggregateRootType;

abstract class AggregateRootType extends BaseAggregateRootType
{
    const VENDOR = 'Honeybee-Tests';

    const PACKAGE = 'GameSchema';

    public function getPackage()
    {
        return self::PACKAGE;
    }

    public function getVendor()
    {
        return self::VENDOR;
    }
}

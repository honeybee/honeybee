<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model;

use Honeybee\Model\Aggregate\AggregateRootType as BaseAggregateRootType;

abstract class AggregateRootType extends BaseAggregateRootType
{
    const VENDOR = 'HoneybeeCmf';

    const PACKAGE = 'AggregateFixtures';

    public function getPackage()
    {
        return self::PACKAGE;
    }

    public function getVendor()
    {
        return self::VENDOR;
    }
}

<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model;

use Honeybee\Model\Aggregate\AggregateRootType as BaseAggregateRootType;

abstract class AggregateRootType extends BaseAggregateRootType
{
    const VENDOR = 'Honeybee-CMF';

    const PACKAGE = 'AggregateFixtures';

    protected $workflow_state_machine;

    public function getPackage()
    {
        return self::PACKAGE;
    }

    public function getVendor()
    {
        return self::VENDOR;
    }

    public function getWorkflowStateMachine()
    {
        return $this->workflow_state_machine;
    }
}

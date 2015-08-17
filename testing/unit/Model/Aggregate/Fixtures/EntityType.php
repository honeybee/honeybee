<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures;

use Honeybee\Model\Aggregate\AggregateRootType;
use Workflux\StateMachine\StateMachineInterface;

abstract class EntityType extends AggregateRootType
{
    const VENDOR = 'Honeybee-CMF';

    const PACKAGE = 'AggregateFixtures';

    protected $workflow_state_machine;

    public function __construct($name, StateMachineInterface $state_machine)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct($name);
    }

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

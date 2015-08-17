<?php

namespace Honeybee\Tests\Projection\Resource\Fixtures;

use Honeybee\Projection\ProjectionType;
use Workflux\StateMachine\StateMachineInterface;

abstract class EntityType extends ProjectionType
{
    const VENDOR = 'Honeybee-CMF';

    const PACKAGE = 'ResourceFixtures';

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

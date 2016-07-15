<?php

namespace Honeybee\Tests\Fixture\GameSchema\Projection;

use Honeybee\Projection\ProjectionType as BaseProjectionType;

abstract class ProjectionType extends BaseProjectionType
{
    const VENDOR = 'Honeybee-Tests';

    const PACKAGE = 'GameSchema';

    /**
     * @var \Workflux\StateMachine\StateMachineInterface $workflow_state_machine
     */
    protected $workflow_state_machine;

    /**
     * @return string
     */
    public function getPackage()
    {
        return self::PACKAGE;
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return self::VENDOR;
    }

    /**
     * @return \Workflux\StateMachine\StateMachineInterface
     */
    public function getWorkflowStateMachine()
    {
        return $this->workflow_state_machine;
    }
}

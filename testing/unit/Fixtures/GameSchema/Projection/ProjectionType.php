<?php

namespace Honeybee\Tests\Fixtures\GameSchema\Projection;

use Honeybee\Projection\ProjectionType as BaseProjectionType;
use Workflux\StateMachine\StateMachineInterface;
use Trellis\Common\OptionsInterface;

abstract class ProjectionType extends BaseProjectionType
{
    const VENDOR = 'Honeybee-Tests';

    const PACKAGE = 'GameSchema';

    const NAMESPACE_PREFIX = '\\Honeybee\\Tests\\Fixtures\\GameSchema\\Projection\\';

    protected $workflow_state_machine;

    public function __construct($name, StateMachineInterface $state_machine, OptionsInterface $options = null)
    {
        $this->workflow_state_machine = $state_machine;

        parent::__construct($name, [], $options);
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

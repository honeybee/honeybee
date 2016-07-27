<?php

namespace Honeybee\Tests\Fixture\TopicSchema\Projection;

use Honeybee\Projection\ProjectionType as BaseProjectionType;

abstract class ProjectionType extends BaseProjectionType
{
    const VENDOR = 'Honeybee-Tests';

    const PACKAGE = 'TopicSchema';

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

<?php

namespace Honeybee\Ui\Renderer;

use Workflux\StateMachine\StateMachineInterface;
use Honeybee\Common\Error\RuntimeError;

abstract class StateMachineRenderer extends Renderer
{
    protected $state_machine;

    protected function validate()
    {
        if (!$this->getPayload('subject') instanceof StateMachineInterface) {
            throw new RuntimeError(
                sprintf('Instance of "%s" necessary.', StateMachineInterface::CLASS)
            );
        }

        $this->state_machine = $this->getPayload('subject');
    }
}

<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Common\Error\RuntimeError;
use Workflux\StateMachine\StateMachine;

class Saga implements SagaInterface
{
    protected $name;

    protected $state_machine;

    public function __construct($name, StateMachine $state_machine)
    {
        $this->name = $name;
        $this->state_machine = $state_machine;
    }

    public function getName($name)
    {
        return $this->name;
    }

    public function proceed(SagaSubjectInterface $saga_subject, $event_name = null)
    {
        $state = $this->state_machine->execute($saga_subject, $event_name);
        $execution_context = $saga_subject->getExecutionContext();
        $command = $execution_context->getParameter('command', false);

        if (!$command && !$state->isFinal()) {
            throw new RuntimeError('Unable to determine the next command within the saga.');
        }

        return $command;
    }

    public function hasFinished(SagaSubjectInterface $saga_subject)
    {
        $state = $this->state_machine->getState($saga_subject->getStateName());

        return $state->isFinal();
    }

    public function hasStarted(SagaSubjectInterface $saga_subject)
    {
        $state_name = $saga_subject->getStateName();
        $state = null;
        if ($state_name) {
            $state = $this->state_machine->getState($state_name);
        }

        return $state && !$state->isInitial();
    }

    public function getStateMachine()
    {
        return $this->state_machine;
    }
}

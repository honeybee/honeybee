<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Workflux\StateMachine\StateMachine;

class Process implements ProcessInterface
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

    public function proceed(ProcessStateInterface $process_state, EventInterface $event = null)
    {
        $execution_context = $process_state->getExecutionContext();

        if ($event) {
            $execution_context->setParameter('incoming_event', $event);
            $state = $this->state_machine->execute($process_state, $event->getType());
            $execution_context->removeParameter('incoming_event', $event);
        } else {
            $state = $this->state_machine->execute($process_state);
        }

        $command = $execution_context->getParameter('command', false);
        if ($command && !$command instanceof CommandInterface) {
            throw new RuntimeError('Given command does not implement required ' . CommandInterface::CLASS);
        }
        if (!$command && !$state->isFinal()) {
            throw new RuntimeError('Unable to determine the next command within the process.');
        }

        $execution_context->removeParameter('command');

        return $command;
    }

    public function hasFinished(ProcessStateInterface $process_state)
    {
        $state = $this->getStateMachineState($process_state);

        return $state && $state->isFinal();
    }

    public function hasStarted(ProcessStateInterface $process_state)
    {
        $state = $this->getStateMachineState($process_state);

        return $state && !$state->isInitial();
    }

    public function getStateMachine()
    {
        return $this->state_machine;
    }

    protected function getStateMachineState(ProcessStateInterface $process_state)
    {
        $state = null;
        if ($state_name = $process_state->getStateName()) {
            $state = $this->state_machine->getState($state_name);
            if (!$state) {
                throw new RuntimeError('Process state can not be resolved: ' . $state_name);
            }
        }

        return $state;
    }
}

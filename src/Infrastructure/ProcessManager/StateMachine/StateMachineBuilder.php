<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Auryn\Injector as DiContainer;
use Workflux\Builder\XmlStateMachineBuilder;
use Workflux\State\State;
use Workflux\Transition\Transition;

class StateMachineBuilder extends XmlStateMachineBuilder
{
    protected $di_container;

    public function __construct(DiContainer $di_container, array $options = [])
    {
        parent::__construct($options);

        $this->di_container = $di_container;
    }

    protected function createState(array $state_definition)
    {
        $state_implementor = isset($state_definition['class']) ? $state_definition['class'] : State::CLASS;
        $this->loadStateImplementor($state_implementor);

        return $this->di_container->make(
            $state_implementor,
            [
                ':name' => $state_definition['name'],
                ':type' => $state_definition['type'],
                ':options' => $state_definition['options']
            ]
        );
    }

    protected function createTransition($state_name, array $transition_definition)
    {
        $target = $transition_definition['outgoing_state_name'];
        $guard_definition = $transition_definition['guard'];

        $guard = null;
        if ($guard_definition) {
            $guard = $this->di_container->make(
                $guard_definition['class'],
                [ ':options' => $guard_definition['options'] ]
            );
        }

        return $this->di_container->make(
            Transition::CLASS,
            [
                ':incoming_state_name_or_names' => $state_name,
                ':outgoing_state_name' => $target,
                ':guard' => $guard
            ]
        );
    }
}

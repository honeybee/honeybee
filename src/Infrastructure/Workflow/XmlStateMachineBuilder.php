<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\ServiceLocatorInterface;
use Workflux\Builder\XmlStateMachineBuilder as BaseXmlStateMachineBuilder;
use Workflux\Error\VerificationError;
use Workflux\Guard\GuardInterface;
use Workflux\Transition\Transition;

/**
 * Wrapper around the Workflux XmlStateMachineBuilder to allow custom guards that
 * get their dependencies constructor injected and to be able to have out own
 * state machine names.
 */
class XmlStateMachineBuilder extends BaseXmlStateMachineBuilder
{
    protected $service_locator;

    /**
     * @param array $options with at least 'name' and 'state_machine_definition' keys set
     * @param ServiceLocatorInterface $service_locator
     */
    public function __construct(array $options, ServiceLocatorInterface $service_locator)
    {
        parent::__construct($options);

        $this->service_locator = $service_locator;
    }

    /**
     * Creates a state transition from the given transition definition.
     *
     * @param string $state_name
     * @param array $transition_definition
     *
     * @return Workflux\Transition\TransitionInterface
     */
    protected function createTransition($state_name, array $transition_definition)
    {
        $target = $transition_definition['outgoing_state_name'];
        $guard_definition = $transition_definition['guard'];

        $guard = null;
        if ($guard_definition) {
            $guard = $this->service_locator->createEntity(
                $guard_definition['class'],
                [
                    ':options' => $guard_definition['options'],
                ]
            );

            if (!$guard instanceof GuardInterface) {
                throw new RuntimeError(
                    sprintf(
                        "Given transition guard '%s' must implement: %s",
                        $guard_definition['class'],
                        GuardInterface::CLASS
                    )
                );
            }
        }

        return new Transition($state_name, $target, $guard);
    }

    /**
     * Sets the state machine's name.
     *
     * @param string $state_machine_name
     *
     * @return Workflux\Builder\StateMachineBuilderInterface
     */
    public function setStateMachineName($state_machine_name)
    {
        $name_regex = '/^[a-zA-Z0-9_\.\-]+$/';

        if (!preg_match($name_regex, $state_machine_name)) {
            throw new VerificationError(
                'Only valid characters for state machine names are [a-zA-Z.-_]. Given: ' . $state_machine_name
            );
        }

        $this->state_machine_name = $state_machine_name;

        return $this;
    }
}

<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\ServiceLocatorInterface;
use Workflux\Builder\XmlStateMachineBuilder as BaseXmlStateMachineBuilder;
use Workflux\Guard\GuardInterface;
use Workflux\Transition\Transition;

class XmlStateMachineBuilder extends BaseXmlStateMachineBuilder
{
    protected $service_locator;

    /**
     * @param array $options
     * @param ServiceLocatorInterface $service_locator
     */
    public function __construct(array $options = [], ServiceLocatorInterface $service_locator)
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
            $guard = $this->service_locator->make(
                $guard_definition['class'],
                [
                    ':options' => $guard_definition['options'],
                ]
            );

            if (!$guard instanceof GuardInterface) {
                throw new RuntimeError(
                    sprintf(
                        "Given transition guard '%s' must implement '%s'.",
                        $guard_definition['class'],
                        GuardInterface::CLASS
                    )
                );
            }
        }

        return new Transition($state_name, $target, $guard);
    }
}

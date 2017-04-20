<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\ServiceLocatorInterface;
use Params\Immutable\ImmutableOptionsInterface;
use Workflux\Builder\XmlStateMachineBuilder as BaseXmlStateMachineBuilder;
use Workflux\Error\VerificationError;
use Workflux\Guard\GuardInterface;
use Workflux\Parser\Xml\StateMachineDefinitionParser;
use Workflux\State\State;
use Workflux\State\StateInterface;
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
     * Parses the configured state machine definition file and returns all parsed state machine definitions.
     *
     * @return array An assoc array with name of a state machine as key and the state machine def as value.
     */
    protected function parseStateMachineDefitions()
    {
        $defs = $this->getOption('state_machine_definition');
        if ($defs instanceof ImmutableOptionsInterface) {
            return $defs->toArray();
        } elseif (is_array($defs)) {
            return $defs;
        }

        if (!is_string($defs)) {
            throw new RuntimeError(
                'Option "state_machine_definition" must be a path to an xml file ' .
                'or already parsed definitions provided as an array.'
            );
        }
        $parser = new StateMachineDefinitionParser();

        return $parser->parse($defs);
    }

    /**
     * Creates a concrete StateInterface instance based on the given state definition.
     *
     * @param array $state_definition
     *
     * @return StateInterface
     */
    protected function createState(array $state_definition)
    {
        $state_implementor = isset($state_definition['class']) ? $state_definition['class'] : State::CLASS;
        $this->loadStateImplementor($state_implementor);

        $state = $this->service_locator->make(
            $state_implementor,
            [
                ':name' => $state_definition['name'],
                ':type' => $state_definition['type'],
                ':options' => $state_definition['options'],
            ]
        );

        if (!$state instanceof StateInterface) {
            throw new RuntimeError(
                sprintf(
                    'Configured custom implementor for state %s does not implement "%s".',
                    $state_definition['name'],
                    StateInterface::CLASS
                )
            );
        }

        return $state;
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

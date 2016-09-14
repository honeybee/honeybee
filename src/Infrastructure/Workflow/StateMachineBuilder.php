<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\ServiceLocatorInterface;

class StateMachineBuilder implements StateMachineBuilderInterface
{
    protected $state_machine_definitions;
    protected $service_locator;

    /**
     * @param array $state_machine_definitions
     * @param ServiceLocatorInterface $service_locator
     */
    public function __construct(
        array $state_machine_definitions,
        ServiceLocatorInterface $service_locator
    ) {
        $this->state_machine_definitions = $state_machine_definitions;
        $this->service_locator = $service_locator;
    }

    /**
     * @param string $name name of state machine to build
     *
     * @return Workflux\StateMachine\StateMachineInterface
     */
    public function build($name)
    {
        if (!isset($this->state_machine_definitions[$name])) {
            throw new RuntimeError('State machine not configured: ' . $name);
        }

        // name is necessary as option here, as there may be multiple state machines in the definition xml file
        $builder = new XmlStateMachineBuilder(
            [
                'name' => $name,
                'state_machine_definition' => $this->state_machine_definitions
            ],
            $this->service_locator
        );

        return $builder->build();
    }
}

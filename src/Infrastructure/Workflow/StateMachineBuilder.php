<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\ServiceLocatorInterface;

class StateMachineBuilder implements StateMachineBuilderInterface
{
    protected $state_machine_config_map;
    protected $service_locator;

    /**
     * @param StateMachineConfigMap $state_machine_config_map
     * @param ServiceLocatorInterface $service_locator
     */
    public function __construct(
        StateMachineConfigMap $state_machine_config_map,
        ServiceLocatorInterface $service_locator
    ) {
        $this->state_machine_config_map = $state_machine_config_map;
        $this->service_locator = $service_locator;
    }

    /**
     * @return Workflux\StateMachine\StateMachineInterface
     */
    protected function buildStateMachineFor($name)
    {
        $state_machine_config = $this->state_machine_config_map->getItem($name);

        $builder = new XmlStateMachineBuilder(
            [
                'name' => $name,
                'state_machine_definition' => $state_machine_config
            ],
            $this->service_locator
        );

        return $builder->build();
    }
}

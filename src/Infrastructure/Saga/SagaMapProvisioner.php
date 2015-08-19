<?php

namespace Honeybee\Infrastructure\Saga;

use Auryn\Injector as DiContainer;
use Honeybee\FrameworkBinding\Agavi\Provisioner\AbstractProvisioner;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\Infrastructure\Saga\Saga;
use Workflux\Builder\XmlStateMachineBuilder;

class SagaMapProvisioner extends AbstractProvisioner
{
    public function build(ServiceDefinitionInterface $service_definition, SettingsInterface $provisioner_settings)
    {
        $saga_map_class = $service_definition->getClass();
        $saga_definitions = $provisioner_settings->get('sagas');

        $this->di_container->share($saga_map_class)->delegate(
            $saga_map_class,
            function (DiContainer $di_container) use ($saga_map_class, $saga_definitions)
            {
                $saga_map = new $saga_map_class();
                foreach ($saga_definitions as $saga_name => $saga_create_infos) {
                    $state_machine_builder = $di_container->make(
                        $saga_create_infos->get('state_machine_builder'),
                        [ ':options' => (array)$saga_create_infos->get('builder_settings', []) ]
                    );

                    $saga_implementor = $saga_create_infos->get('class', Saga::CLASS);
                    $saga_map->setItem(
                        $saga_name,
                        $di_container->make(
                            $saga_implementor,
                            [ ':name' => $saga_name,  ':state_machine' => $state_machine_builder->build()
                        ])
                    );
                }

                return $saga_map;
            }
        );
    }
}
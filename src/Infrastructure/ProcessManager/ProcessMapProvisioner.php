<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Auryn\Injector as DiContainer;
use Honeybee\FrameworkBinding\Agavi\Provisioner\AbstractProvisioner;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Honeybee\Infrastructure\ProcessManager\Process;
use Workflux\Builder\XmlStateMachineBuilder;

class ProcessMapProvisioner extends AbstractProvisioner
{
    public function build(ServiceDefinitionInterface $service_definition, SettingsInterface $provisioner_settings)
    {
        $process_map_class = $service_definition->getClass();
        $process_definitions = $provisioner_settings->get('processes');

        $this->di_container->share($process_map_class)->delegate(
            $process_map_class,
            function (DiContainer $di_container) use ($process_map_class, $process_definitions)
            {
                $process_map = new $process_map_class();
                foreach ($process_definitions as $process_name => $process_create_infos) {
                    $state_machine_builder = $di_container->make(
                        $process_create_infos->get('state_machine_builder'),
                        [ ':options' => (array)$process_create_infos->get('builder_settings', []) ]
                    );

                    $process_implementor = $process_create_infos->get('class', Process::CLASS);
                    $process_map->setItem(
                        $process_name,
                        $di_container->make(
                            $process_implementor,
                            [ ':name' => $process_name,  ':state_machine' => $state_machine_builder->build()
                        ])
                    );
                }

                return $process_map;
            }
        );
    }
}
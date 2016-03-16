<?php

namespace Honeybee\Infrastructure\Job;

use Auryn\Injector as DiContainer;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class JobFactory
{
    protected $service_locator;

    protected $config;

    public function __construct(
        ServiceLocatorInterface $service_locator,
        ConfigInterface $config
    ) {
        $this->service_locator = $service_locator;
        $this->config = $config;
    }

    public function create($job_name, array $job_state)
    {
        $job_config = $this->getConfig($job_name);

        $job_state[Job::OBJECT_TYPE] = $job_config['class'];
        return $this->service_locator->createEntity(
            $job_config['class'],
            [
                ':state' => $job_state,
                ':settings' => $job_config['settings'],
                ':strategy' => $this->buildJobStrategy($job_config['strategy'])
            ]
        );
    }

    public function getConfig($job_name)
    {
        $job_config = $this->config->get($job_name);

        if (!$job_config) {
            throw new RuntimeError(sprintf('Configuration for job "%s" was not found.', $job_name));
        }

        return $job_config;
    }

    protected function buildJobStrategy(SettingsInterface $strategy_config)
    {
        $strategy_implementor = $strategy_config['implementor'];

        $retry_strategy = $this->service_locator->createEntity(
            $strategy_config['retry']['implementor'],
            [ ':settings' => $strategy_config['retry']['settings'] ]
        );

        $failure_strategy = $this->service_locator->createEntity(
            $strategy_config['failure']['implementor'],
            [ ':settings' => $strategy_config['failure']['settings'] ]
        );

        return new $strategy_implementor($retry_strategy, $failure_strategy);
    }
}

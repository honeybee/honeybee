<?php

namespace Honeybee;

use Auryn\Injector as DiContainer;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;

final class ServiceLocator implements ServiceLocatorInterface
{
    private $di_container;

    private $service_map;

    public function __construct(DiContainer $di_container, ServiceDefinitionMap $service_map)
    {
        $this->di_container = $di_container;
        $this->service_map = $service_map;
    }

    public function get($service_key)
    {
        if (!$this->service_map->hasKey($service_key)) {
            throw new RuntimeError(sprintf('No service found for given service-key: "%s".', $service_key));
        }
        $service_definition = $this->service_map->getItem($service_key);
        return $this->di_container->make($service_definition->getClass());
    }

    public function has($service_key)
    {
        return $this->service_map->hasKey($service_key);
    }

    public function __call($method, array $args)
    {
        if (preg_match('/^get(\w+)$/', $method, $matches)) {
            $service_key = "honeybee.infrastructure.".StringToolkit::asSnakeCase($matches[1]);
            return $this->get($service_key);
        }
    }

    public function make($implementor, array $state = [])
    {
        return $this->di_container->make($implementor, $state);
    }
}

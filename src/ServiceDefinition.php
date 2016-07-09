<?php

namespace Honeybee;

use Honeybee\Infrastructure\Config\ArrayConfig;

class ServiceDefinition implements ServiceDefinitionInterface
{
    protected $class;

    protected $provisioner;

    public function __construct(array $state = [])
    {
        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function getProvisioner()
    {
        return $this->provisioner;
    }

    public function hasProvisioner()
    {
        return isset($this->provisioner);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function hasClass()
    {
        return isset($this->class);
    }

    public function getConfig()
    {
        return new ArrayConfig($this->options);
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}

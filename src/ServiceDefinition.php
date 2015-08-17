<?php

namespace Honeybee;

use Trellis\Common\Configurable;
use Honeybee\Infrastructure\Config\ArrayConfig;

class ServiceDefinition extends Configurable implements ServiceDefinitionInterface
{
    protected $class;

    protected $provisioner;

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
        return new ArrayConfig($this->options->toArray());
    }
}

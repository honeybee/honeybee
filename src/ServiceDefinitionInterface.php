<?php

namespace Honeybee;

use Honeybee\Infrastructure\Config\ArrayConfig;

interface ServiceDefinitionInterface
{
    public function getProvisioner();

    /**
     * @return boolean true when a provisioner is set
     */
    public function hasProvisioner();

    /**
     * @return string full qualified class name of the service
     */
    public function getClass();

    /**
     * @return boolean true when a fqcn is present
     */
    public function hasClass();

    /**
     * @return ArrayConfig
     */
    public function getConfig();
}

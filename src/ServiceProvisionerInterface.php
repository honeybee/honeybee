<?php

namespace Honeybee;

/**
 * Provisioners must prepare all the services and classes Honeybee needs.
 */
interface ServiceProvisionerInterface
{
    public function provision();
}

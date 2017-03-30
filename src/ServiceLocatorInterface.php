<?php

namespace Honeybee;

use Honeybee\EntityInterface;
use Psr\Container\ContainerInterface;

/**
 * Interface for convenience wrapper classes that provide access
 * to well known and often used Honeybee services.
 */
interface ServiceLocatorInterface extends ContainerInterface
{
    public function make(string $implementor, array $state);
}

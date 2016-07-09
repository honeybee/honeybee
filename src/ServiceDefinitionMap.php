<?php

namespace Honeybee;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

/**
 * Holds a map with service keys and the implementor to use for creation of
 * those services.
 *
 * Example:
 *
 * "authentication" => "Honeybee\Infrastructure\Security\Auth\StandardAuthProvider"
 */
class ServiceDefinitionMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $service_definitions = [])
    {
        parent::__construct(ServiceDefinitionInterface::CLASS, $service_definitions);
    }
}

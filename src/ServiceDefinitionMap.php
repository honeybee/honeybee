<?php

namespace Honeybee;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Options;

/**
 * Holds a map with service keys and the implementor to use for creation of
 * those services.
 *
 * Example:
 *
 * "authentication" => "Honeybee\Infrastructure\Security\Auth\StandardAuthProvider"
 */
class ServiceDefinitionMap extends TypedMap
{
    protected $options;

    public function __construct(Options $options = null)
    {
        $this->options = $options;
    }

    public function getOption($option_key)
    {
        return $this->options->get($option_key);
    }

    public function hasOption($option_key)
    {
        return $this->options->has($option_key);
    }

    protected function getItemImplementor()
    {
        return ServiceDefinitionInterface::CLASS;
    }
}

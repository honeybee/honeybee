<?php

namespace Honeybee\Infrastructure\Config;

use Honeybee\Common\Error\ConfigError;

/**
 * Array specific implementation of the BaseConfig base.
 *
 * Provides a strategy for handling simple data configuration as
 * an associative array. Extend this class, provide your required
 * settings via {@see BaseConfig::getRequiredSettings()} and pass
 * in your settings data to the constructor.
 */
class ArrayConfig extends Config
{
    /**
     * Load the given $config_source and return an array representation.
     *
     * @param array $config_source
     *
     * @return array
     *
     * @throws ConfigError if $config_source is not an array
     */
    protected function load($config_source)
    {
        if (!is_array($config_source)) {
            throw new ConfigError("The given config source must be an array.");
        }

        return $config_source;
    }
}

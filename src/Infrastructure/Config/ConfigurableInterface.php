<?php

namespace Honeybee\Infrastructure\Config;

/**
 * ConfigInterface implementations provide access to their configration data.
 */
interface ConfigurableInterface
{
    /**
     * Returns the configuration.
     *
     * @return ConfigInterface
     */
    public function getConfig();
}

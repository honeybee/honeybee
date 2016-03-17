<?php

namespace Honeybee\Infrastructure\Config;

use Honeybee\Common\Error\ConfigError;

/**
 * The BaseConfig class is an abstract implementation of the ConfigInterface.
 * It fully exposes the required interface methods and defines the strategy for loading
 * a given config source.
 */
abstract class Config implements ConfigInterface
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * Load the given $config_source and return an array representation.
     *
     * @param $config_source
     *
     * @return array
     */
    abstract protected function load($config_source);

    /**
     * Create a new BaseConfig instance for the given $config_source.
     *
     * @param mixed $config_source
     */
    public function __construct($config_source)
    {
        $this->init($config_source);
    }

    /**
     * Returns whether the setting key exists or not.
     *
     * @param mixed $key key to check
     *
     * @return bool true, if key exists; false otherwise
     */
    public function has($key)
    {
        return $this->settings->has($key);
    }

    /**
     * Returns the value for the given key.
     *
     * @param mixed $key key to get value of
     * @param mixed $default value to return if key doesn't exist
     *
     * @return mixed value for that key or default given
     */
    public function get($key, $default = null)
    {
        return $this->settings->get($key, $default);
    }

    /**
     * Returns all the keys from the first config level.
     *
     * @return array root level key names
     */
    public function getKeys()
    {
        return $this->settings->getKeys();
    }

    /**
     * Allows to search for specific setting values via JMESPath expressions.
     *
     * Some example expressions as a quick start:
     *
     * - "nested.key"           returns the value of the nested "key"
     * - "nested.*"             returns all values available under the "nested" key
     * - "*.key"                returns all values of "key"s on any second level array
     * - "[key, nested.key]"    returns first level "key" value and the first value of the "nested" key array
     * - "[key, nested[0]"      returns first level "key" value and the first value of the "nested" key array
     * - "nested.key || key"    returns the value of the first matching expression
     *
     * @see http://jmespath.readthedocs.org/en/latest/ and https://github.com/mtdowling/jmespath.php
     *
     * @param string $expression JMESPath expression to evaluate on stored data
     *
     * @return mixed|null data in various types (scalar, array etc.) depending on the found results
     *
     * @throws \JmesPath\SyntaxErrorException on invalid expression syntax
     * @throws \RuntimeException e.g. if JMESPath cache directory cannot be written
     * @throws \InvalidArgumentException e.g. if JMESPath builtin functions can't be called
     */
    public function getValues($expression = '*')
    {
        return $this->settings->getValues($expression);
    }

    /**
     * Returns the settings as an associative array.
     *
     * @return array with all settings
     */
    public function toArray()
    {
        return $this->settings->toArray();
    }

    /**
     * Return this object's immutable settings instance.
     *
     * @return Settings instance used internally
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Initialize this BaseConfig instance with the given $config_source.
     * After this method has completed we are ready to provide settings.
     *
     * @param mixed $config_source
     */
    protected function init($config_source)
    {
        $settings = new Settings($this->load($config_source));

        $this->validateConfig($settings);

        $this->settings = $settings;
    }

    /**
     * Validate the given settings against any required rules.
     * This basic implementation just makes sure,
     * that all required settings are in place.
     *
     * @param SettingsInterface $settings
     *
     * @throws ConfigError
     */
    protected function validateConfig(SettingsInterface $settings)
    {
        foreach ($this->getRequiredSettings() as $required_setting) {
            if (is_null($settings->getValues($required_setting))) {
                throw new ConfigError(
                    "Missing mandatory setting '" . $required_setting . "' for config."
                );
            }
        }
    }

    /**
     * Return an array of settings that are to be considered as mandatory for this instance.
     * An exception will occur upon initialization if a required setting is not available after loading.
     *
     * @return array with key expressions understood by Settings::getValues()
     */
    protected function getRequiredSettings()
    {
        return [];
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ConfigInterface;

abstract class Connector implements ConnectorInterface
{
    protected $name;

    protected $config;

    protected $connection = null;

    /**
     * Creates the client used to work on the actual connection. Whether it creates
     * an actual connection at that very moment is up to the implementation. Whether
     * repeated calls of this method actually recreate the client/connection is up
     * to the implementation as well.
     *
     * @return mixed the created connection/client
     */
    abstract public function connect();

    /**
     * @param string $name name of connection
     * @param ConfigInterface $config configuration to use for the connection
     */
    public function __construct($name, ConfigInterface $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Disconnects upon connector destruction.
     */
    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }
    }

    /**
     * @return string connection name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed the created connection/client
     */
    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connect();
        }

        return $this->connection;
    }

    /**
     * Returns true when the connector's connection is initialized. Usually
     * this means a client was created that will be used to do the actual
     * work.
     *
     * @return boolean true if connector is connected; false otherwise.
     */
    public function isConnected()
    {
        return ($this->connection !== null);
    }

    /**
     * Unsets the connection/client. Depending on the type of client further
     * operations need to be done upon disconnecting. Overriding this method
     * may be a good idea in those cases.
     *
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->connection = null;
        }
    }

    /**
     * @return ConfigInterface connector configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Depending on the type of client and how the connection works this method
     * may return UNKNOWN Status or create an actual connection and check whether
     * it works as expected. Status checks should strive to be fast though.
     *
     * Whether to do actual status checks of the underlying connection is entirely
     * up to the connector.
     *
     * @return Status of this connector
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        return Status::unknown($this);
    }

    /**
     * @param string $setting_name config setting name to check for existance
     * @param string $message to use in ConfigError that is thrown upon non-existant settings
     *
     * @return static
     *
     * @throws ConfigError when the given setting name doesn't exist in the config
     */
    protected function needs($setting_name, $message = null)
    {
        if (!$this->config->has($setting_name)) {
            $text = $message ?: sprintf('Missing setting "%s" in connector "%s".', $setting_name, $this->name);
            throw new ConfigError($text);
        }

        return $this;
    }
}

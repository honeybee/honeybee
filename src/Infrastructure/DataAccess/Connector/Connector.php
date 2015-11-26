<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ConfigInterface;

abstract class Connector implements ConnectorInterface
{
    protected $name;

    protected $config;

    protected $is_connected;

    protected $connection;

    public function __construct($name, ConfigInterface $config)
    {
        $this->name = $name;
        $this->config = $config;
        $this->is_connected = false;
    }

    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getConnection()
    {
        if (!$this->isConnected()) {
            $this->connection = $this->connect();
            $this->is_connected = true;
        }

        return $this->connection;
    }

    /**
     * @return boolean true if connector is connected; false otherwise.
     */
    public function isConnected()
    {
        return $this->is_connected;
    }

    /**
     * @return void
     */
    public function disconnect()
    {
        if ($this->is_connected) {
            $this->connection = null;
            $this->is_connected = false;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function needs($setting_name, $message = null)
    {
        if (!$this->config->has($setting_name)) {
            $text = $message ?: sprintf('Missing setting "%s" in connector "%s".', $setting_name, $this->name);
            throw new ConfigError($text);
        }

        return $this;
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\ConfigError;
use PhpAmqpLib\Connection\AMQPLazyConnection;

class RabbitMqConnector extends Connector
{
    const DEFAULT_HOST = 'localhost';

    const DEFAULT_PORT = 5672;

    /**
     * @return mixed
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return $this->client;
        }

        if (!$this->config->has('user')) {
            throw new ConfigError("Missing user parameter within RabbitMq configuration");
        }

        if (!$this->config->has('password')) {
            throw new ConfigError("Missing password parameter within RabbitMq configuration");
        }

        $host = $this->config->get('host', self::DEFAULT_HOST);
        $port = $this->config->get('port', self::DEFAULT_PORT);
        $user = $this->config->get('user');
        $password = $this->config->get('password');

        return new AMQPLazyConnection($host, $port, $user, $password);
    }

    /**
     * @return void
     */
    public function disconnect()
    {
        if ($this->isConnected()) {
            $this->connection->close();
        }

        parent::disconnect();
    }
}

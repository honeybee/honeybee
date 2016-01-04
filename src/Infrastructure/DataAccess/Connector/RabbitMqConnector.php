<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use PhpAmqpLib\Connection\AMQPLazyConnection;

class RabbitMqConnector extends Connector
{
    const DEFAULT_HOST = 'localhost';

    const DEFAULT_PORT = 5672;

    /**
     * @return mixed
     */
    protected function connect()
    {
        // don't recreate the connection by default
        if ($this->isConnected()) {
            return $this->connection;
        }

        $this->needs('user')->needs('password');

        // https://github.com/videlalvaro/php-amqplib/blob/master/PhpAmqpLib/Connection/AMQPStreamConnection.php#L8-L39

        $host = $this->config->get('host', self::DEFAULT_HOST);
        $port = $this->config->get('port', self::DEFAULT_PORT);
        $user = $this->config->get('user');
        $password = $this->config->get('password');

        $vhost = $this->config->get('vhost', '/');
        $insist = $this->config->get('insist', false);
        $login_method = $this->config->get('login_method', 'AMQPLAIN');
        $login_response = null;
        $locale = $this->config->get('locale', 'en_US');
        $connection_timeout = (int)$this->config->get('connection_timeout', 3);
        $read_write_timeout = (int)$this->config->get('read_write_timeout', 3);
        $context = null;
        $keepalive = $this->config->get('keepalive', true);

        // setting this to NULL may lead to using the server proposed value?
        // https://github.com/videlalvaro/php-amqplib/blob/master/PhpAmqpLib/Connection/AbstractConnection.php#L803-L806
        $heartbeat = $this->config->get('heartbeat', 0);

        return new AMQPLazyConnection(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat
        );
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

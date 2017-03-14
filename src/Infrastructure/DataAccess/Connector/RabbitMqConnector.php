<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Exception;
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

        return $this->getFreshConnection();
    }

    protected function getFreshConnection()
    {
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

    /**
     * @return array
     */
    public function getFromAdminApi($endpoint)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->buildAdminUrl($endpoint));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getAdminCredentials());
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    /**
     * @return array
     */
    public function putToAdminApi($endpoint, array $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->buildAdminUrl($endpoint));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'content-type:application/json' ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getAdminCredentials());
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    protected function buildAdminUrl($endpoint)
    {
        return sprintf(
            "%s://%s:%s%s",
            $this->config->get('transport', 'http'),
            $this->config->get('host', 'localhost'),
            $this->config->get('admin_port', '15672'),
            $endpoint
        );
    }

    protected function getAdminCredentials()
    {
        return sprintf(
            '%s:%s',
            $this->config->get('admin_user', $this->config->get('user')),
            $this->config->get('admin_password', $this->config->get('password'))
        );
    }

    /**
     * Tries to connect to rabbitmq server and get the server properties.
     *
     * @return Status of this connector
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        try {
            $conn = $this->getFreshConnection();
            $conn->reconnect();
            $info = $conn->getServerProperties();
            return Status::working($this, [
                'message' => 'Could reconnect to rabbitmq server.',
                'server_properties' => $info
            ]);
        } catch (Exception $e) {
            error_log(
                '[' . static::CLASS . '] Error on status check: ' . $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            return Status::failing($this, [ 'error' => $e->getMessage() ]);
        }
    }
}

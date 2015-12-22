<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

interface ConnectorInterface
{
    /**
     * @return string connection name
     */
    public function getName();

    /**
     * Returns the existing connection/client.
     *
     * When the connector is not connected yet, it must create the connection/client.
     *
     * @return mixed the (created) connection/client
     */
    public function getConnection();

    /**
     * @return boolean true when the connection/client was created; false otherwise.
     */
    public function isConnected();

    /**
     * Unsets the connection/client. Depending on the type of client further
     * operations may be necessary or advised upon disconnecting.
     *
     * @return void
     */
    public function disconnect();

    /**
     * @return ConfigInterface connector configuration
     */
    public function getConfig();

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
    public function getStatus();
}

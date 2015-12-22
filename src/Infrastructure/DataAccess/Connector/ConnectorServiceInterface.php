<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

interface ConnectorServiceInterface
{
    /**
     * @param string $name name of connector to return the connection from
     *
     * @return mixed instance of the connector's connection/client
     */
    public function getConnection($name);

    /**
     * @param string $name name of connector to return
     *
     * @return ConnectorInterface instance
     */
    public function getConnector($name);

    /**
     * @return ConnectorMap
     */
    public function getConnectorMap();

    /**
     * @param string $name name of connector to disconnect
     */
    public function disconnect($name);
}

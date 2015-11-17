<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;

class ConnectorService implements ConnectorServiceInterface
{
    protected $connector_map;

    public function __construct(ConnectorMap $connector_map)
    {
        $this->connector_map = $connector_map;
    }

    /**
     * @param string $name name of connector to return
     *
     * @return ConnectorInterface instance
     */
    public function getConnector($name)
    {
        if (!$this->connector_map->hasKey($name)) {
            throw new RuntimeError(
                sprintf('Can\'t find connector with name: %s. Maybe a typo within the connections.xml?', $name)
            );
        }

        return $this->connector_map->getItem($name);
    }

    public function getConnectorMap()
    {
        return $this->connector_map;
    }

    /**
     * @param string $name name of connector to return the connection from
     *
     * @return mixed instance of the connector's connection/client
     */
    public function getConnection($name)
    {
        return $this->getConnector($name)->getConnection();
    }

    /**
     * @param string $name name of connector to connect
     */
    public function connect($name)
    {
        $this->getConnector($name)->connect();
    }

    /**
     * @param string $name name of connector to disconnect
     */
    public function disconnect($name)
    {
        $this->getConnector($name)->disconnect();
    }
}

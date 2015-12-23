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

    /**
     * @return ConnectorMap
     */
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
     * @param string $name name of connector to disconnect
     */
    public function disconnect($name)
    {
        $this->getConnector($name)->disconnect();
    }

    /**
     * Generates a report about the status of the configured connections and returns it.
     *
     * BEWARE! The report may be verbose and most likely includes sensitive information
     * that shouldn't be output publicly or be accessible to unauthorized persons.
     *
     * This may take some time to generate depending on the connectors being used.
     *
     * @return StatusReport newly generated status report
     */
    public function getStatusReport()
    {
        return StatusReport::generate($this->connector_map);
    }
}

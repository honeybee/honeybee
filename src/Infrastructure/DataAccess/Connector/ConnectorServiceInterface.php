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
    public function getStatusReport();
}

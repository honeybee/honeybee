<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

interface ConnectorInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed
     */
    public function getConnection();

    /**
     * @return boolean true if connector is connected; false otherwise.
     */
    public function isConnected();

    /**
     * @return mixed the created connection/client
     */
    public function connect();

    /**
     * @return void
     */
    public function disconnect();

    public function getConfig();
}

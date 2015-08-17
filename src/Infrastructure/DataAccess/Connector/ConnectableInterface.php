<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

interface ConnectableInterface
{
    /**
     * @return ConnectorInterface
     */
    public function getConnector();
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Memory;

use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Infrastructure\DataAccess\Connector\Status;
use Trellis\Common\Collection\Map;

class ArrayConnector extends Connector
{
    protected function connect()
    {
        return new Map;
    }

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
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        if ($this->connect() instanceof Map) {
            return Status::working($this);
        }

        return Status::failing($this);
    }
}

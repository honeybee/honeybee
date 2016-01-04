<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Gaufrette;

use Gaufrette\Adapter;
use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;

class LocalConnector extends Connector
{
    protected $adapter;

    /**
     * @return Filesystem with a Local adapter
     */
    protected function connect()
    {
        $this->needs('directory');

        $mode = $this->config->get('mode', '0644');
        if (is_string($mode)) {
            $mode = intval($mode, 8);
        }

        $this->adapter = new Local(
            $this->config->get('directory'),
            $this->config->get('create', true),
            $this->config->get('mode', 0644)
        );

        return new Filesystem($this->adapter);
    }
}

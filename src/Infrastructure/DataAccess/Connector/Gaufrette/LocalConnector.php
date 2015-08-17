<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Gaufrette;

use Gaufrette\Adapter;
use Gaufrette\Adapter\Local;
use Gaufrette\Filesystem;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Common\Error\ConfigError;

class LocalConnector extends Connector
{
    protected $adapter;

    /**
     * @return Filesystem with a Local adapter
     */
    public function connect()
    {
        if (!$this->config->has('directory')) {
            throw new ConfigError('There must be a "directory" setting that defines where to store files.');
        }

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

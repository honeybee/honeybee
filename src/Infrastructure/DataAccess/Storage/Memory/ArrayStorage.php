<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Memory;

use Honeybee\Common\Error\ConfigError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Connector\Memory\ArrayConnector;
use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Psr\Log\LoggerInterface;

class ArrayStorage extends Storage
{
    public function __construct(ConnectorInterface $connector, ConfigInterface $config, LoggerInterface $logger)
    {
        if ($connector instanceof ArrayConnector) {
            throw new ConfigError(
                'Unsupported connector type given to ' . __CLASS__ . ', only ArrayConnector supported'
            );
        }

        parent::__construct($connector, $config, $logger);
    }
}

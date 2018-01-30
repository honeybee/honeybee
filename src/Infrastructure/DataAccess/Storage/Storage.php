<?php

namespace Honeybee\Infrastructure\DataAccess\Storage;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\ConfigurableInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectableInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Psr\Log\LoggerInterface;
use Trellis\Common\BaseObject;

abstract class Storage extends BaseObject implements ConnectableInterface, ConfigurableInterface
{
    const DOMAIN_FIELD_ID = 'identifier';

    const DOMAIN_FIELD_VERSION = 'revision';

    protected $connector;

    protected $config;

    protected $logger;

    public function __construct(ConnectorInterface $connector, ConfigInterface $config, LoggerInterface $logger)
    {
        $this->connector = $connector;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getConnector()
    {
        return $this->connector;
    }

    public function getConfig()
    {
        return $this->config;
    }
}

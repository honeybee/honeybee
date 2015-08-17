<?php

namespace Honeybee\Infrastructure\DataAccess\Finder;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Psr\Log\LoggerInterface;
use Trellis\Common\Object;

abstract class Finder extends Object implements FinderInterface
{
    const DOMAIN_FIELD_ID = 'identifier';

    const DOMAIN_FIELD_VERSION = 'revision';

    protected $connector;

    protected $config;

    protected $logger;

    public function __construct(
        ConnectorInterface $connector,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->connector = $connector;
        $this->config = $config;
        $this->logger = $logger;
    }
}

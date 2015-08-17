<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;

class DomainEventProjector extends EventHandler
{
    protected $data_access_service;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        DataAccessServiceInterface $data_access_service
    ) {
        parent::__construct($config, $logger);

        $this->data_access_service = $data_access_service;
    }

    public function handleEvent(EventInterface $event)
    {
        $writer_name = $this->config->get('storage_writer');
        $this->data_access_service->writeTo($writer_name, $event);
    }
}

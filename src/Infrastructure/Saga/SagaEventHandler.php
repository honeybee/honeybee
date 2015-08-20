<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;

class SagaEventHandler extends EventHandler
{
    protected $saga_service;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        SagaServiceInterface $saga_service
    ) {
        parent::__construct($config, $logger);

        $this->saga_service = $saga_service;
    }

    public function handleEvent(EventInterface $event)
    {
        $this->saga_service->continueSaga($event);
    }
}


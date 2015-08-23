<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;

class ProcessListener extends EventHandler
{
    protected $process_manager;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        ProcessManagerInterface $process_manager
    ) {
        parent::__construct($config, $logger);

        $this->process_manager = $process_manager;
    }

    public function handleEvent(EventInterface $event)
    {
var_dump($event->getType());
        $this->process_manager->continueProcess($event);
    }
}


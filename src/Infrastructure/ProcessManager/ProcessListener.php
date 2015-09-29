<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessCompletedEvent;
use Psr\Log\LoggerInterface;
use Shrink0r\Monatic\Maybe;

class ProcessListener extends EventHandler
{
    protected $process_manager;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        EventBusInterface $event_bus,
        ProcessManagerInterface $process_manager
    ) {
        parent::__construct($config, $logger);

        $this->event_bus = $event_bus;
        $this->process_manager = $process_manager;
    }

    public function handleEvent(EventInterface $event)
    {
        $meta_data = Maybe::unit($event->getMetaData());
        $process_uuid = $meta_data->process_uuid->get();
        $process_name = $meta_data->process_name->get();

        if ($process_uuid && $process_name) {
            $process_state = $this->process_manager->continueProcess($event);
            if ($this->process_manager->hasCompleted($process_state)) {
                $this->event_bus->distribute(
                    'honeybee.events.infrastructure',
                    new ProcessCompletedEvent($process_state)
                );
            }
        }
    }
}

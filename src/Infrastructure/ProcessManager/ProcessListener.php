<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessCompletedEvent;
use Psr\Log\LoggerInterface;
use Shrink0r\Monatic\Maybe;
use Ramsey\Uuid\Uuid;

class ProcessListener extends EventHandler
{
    protected $event_bus;

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
        $metadata = Maybe::unit($event->getMetadata());
        $process_uuid = $metadata->process_uuid->get();
        $process_name = $metadata->process_name->get();

        if ($process_uuid && $process_name) {
            $process_state = $this->process_manager->continueProcess($event);
            if ($this->process_manager->hasCompleted($process_state)) {
                $this->event_bus->distribute(
                    ChannelMap::CHANNEL_INFRA,
                    new ProcessCompletedEvent(
                        [
                            'uuid' => Uuid::uuid4()->toString(),
                            'process_state' => $process_state
                        ]
                    )
                );
            }

            return $process_state;
        }

        return null;
    }
}

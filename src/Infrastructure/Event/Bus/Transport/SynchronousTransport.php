<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class SynchronousTransport extends EventTransport
{
    protected $event_bus;

    public function __construct($name, EventBusInterface $event_bus)
    {
        parent::__construct($name);

        $this->event_bus = $event_bus;
    }

    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null)
    {
        $this->event_bus->executeHandlers($channel_name, $event, $subscription_index);
    }
}

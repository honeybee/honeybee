<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Object;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Bus\Transport\EventTransportInterface;
use Honeybee\Infrastructure\Event\EventHandlerList;

class EventSubscription extends Object implements EventSubscriptionInterface
{
    protected $event_filters;

    protected $event_transport;

    protected $event_handlers;

    protected $activated;

    public function __construct(
        EventTransportInterface $event_transport,
        EventFilterList $event_filters,
        EventHandlerList $event_handlers,
        $activated
    ) {
        $this->event_transport = $event_transport;
        $this->event_handlers = $event_handlers;
        $this->event_filters = $event_filters;
        $this->activated = (bool)$activated;
    }

    public function getEventFilters()
    {
        return $this->event_filters;
    }

    public function getEventHandlers()
    {
        return $this->event_handlers;
    }

    public function getEventTransport()
    {
        return $this->event_transport;
    }

    public function isActivated()
    {
        return $this->activated;
    }
}

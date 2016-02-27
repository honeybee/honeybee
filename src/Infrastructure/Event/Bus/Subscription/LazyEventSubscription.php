<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventFilterList;
use Honeybee\Infrastructure\Event\Bus\Transport\EventTransportInterface;
use Honeybee\Infrastructure\Event\Bus\Strategy\EventStrategy;
use Honeybee\Infrastructure\Event\EventHandlerInterface;
use Honeybee\Infrastructure\Event\EventHandlerList;

class LazyEventSubscription extends EventSubscription
{
    protected $event_handlers_callback;

    protected $event_filters_callback;

    protected $event_transport_callback;

    protected $event_strategy_callback;

    public function __construct(
        Closure $event_handlers_callback,
        Closure $event_filters_callback,
        Closure $event_transport_callback,
        Closure $event_strategy_callback,
        $activated
    ) {
        $this->event_transport_callback = $event_transport_callback;
        $this->event_filters_callback = $event_filters_callback;
        $this->event_handlers_callback = $event_handlers_callback;
        $this->event_strategy_callback = $event_strategy_callback;
        $this->activated = $activated;
    }

    public function getEventHandlers()
    {
        if (!$this->event_handlers) {
            $this->event_handlers = $this->createEventHandlers();
        }
        return $this->event_handlers;
    }

    public function getEventFilters()
    {
        if (!$this->event_filters) {
            $this->event_filters = $this->createEventFilters();
        }
        return $this->event_filters;
    }

    public function getEventTransport()
    {
        if (!$this->event_transport) {
            $this->event_transport = $this->createEventTransport();
        }
        return $this->event_transport;
    }

    public function getEventStrategy()
    {
        if (!$this->event_strategy) {
            $this->event_strategy = $this->createEventStrategy();
        }
        return $this->event_strategy;

    }

    protected function createEventHandlers()
    {
        $create_function = $this->event_handlers_callback;
        $event_handlers = $create_function();

        if (!$event_handlers instanceof EventHandlerList) {
            throw new RuntimeError(
                sprintf(
                    "Invalid event-handler list type given: %s, expected instance of %s",
                    get_class($event_handlers),
                    EventHandlerList::CLASS
                )
            );
        }

        return $event_handlers;
    }

    protected function createEventFilters()
    {
        $create_function = $this->event_filters_callback;
        $event_filters = $create_function();

        if (!$event_filters instanceof EventFilterList) {
            throw new RuntimeError(
                sprintf(
                    "Invalid event-filter list type given: %s, expected instance of %s",
                    get_class($event_filters),
                    EventFilterList::CLASS
                )
            );
        }

        return $event_filters;
    }

    protected function createEventTransport()
    {
        $create_function = $this->event_transport_callback;
        $event_transport = $create_function();

        if (!$event_transport instanceof EventTransportInterface) {
            throw new RuntimeError(
                sprintf(
                    "Invalid event-transport type given: %s, expected instance of %s",
                    get_class($event_transport),
                    EventTransportInterface::CLASS
                )
            );
        }

        return $event_transport;
    }

    protected function createEventStrategy()
    {
        $create_function = $this->event_strategy_callback;
        $event_strategy = $create_function();

        if (!$event_strategy instanceof EventStrategy) {
            throw new RuntimeError(
                sprintf(
                    "Invalid event strategy given: %s, expected instance of %s",
                    get_class($event_strategy),
                    EventStrategy::CLASS
                )
            );
        }

        return $event_strategy;
    }
}

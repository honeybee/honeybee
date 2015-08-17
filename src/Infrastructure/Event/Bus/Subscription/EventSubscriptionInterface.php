<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

interface EventSubscriptionInterface
{
    public function getEventFilters();

    public function getEventHandlers();

    public function getEventTransport();
}

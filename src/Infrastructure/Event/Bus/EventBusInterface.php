<?php

namespace Honeybee\Infrastructure\Event\Bus;

use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionInterface;
use Honeybee\Infrastructure\Event\EventInterface;

interface EventBusInterface
{
    public function executeHandlers($channel_name, EventInterface $event);

    public function distribute($channel_name, EventInterface $event);

    public function subscribe($channel_name, EventSubscriptionInterface $subscription);

    public function unsubscribe($channel_name, EventSubscriptionInterface $subscription);

    public function getChannels();

    public function getChannel($channel_name);

    public function getSubscriptions($channel_name);
}

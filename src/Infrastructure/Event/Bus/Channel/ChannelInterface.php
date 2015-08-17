<?php

namespace Honeybee\Infrastructure\Event\Bus\Channel;

use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionInterface;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionList;

interface ChannelInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return EventSubscriptionList
     */
    public function getSubscriptions();

    public function subscribe(EventSubscriptionInterface $subscription);

    public function unsubscribe(EventSubscriptionInterface $subscription);
}

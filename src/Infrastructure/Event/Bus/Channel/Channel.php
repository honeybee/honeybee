<?php

namespace Honeybee\Infrastructure\Event\Bus\Channel;

use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionInterface;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionList;
use Trellis\Common\BaseObject;

class Channel extends BaseObject implements ChannelInterface
{
    protected $name;

    protected $subscriptions;

    public function __construct($name)
    {
        $this->name = $name;
        $this->subscriptions = new EventSubscriptionList();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function subscribe(EventSubscriptionInterface $subscription)
    {
        $this->subscriptions->addItem($subscription);
    }

    public function unsubscribe(EventSubscriptionInterface $subscription)
    {
        $this->subscriptions->removeItem($subscription);
    }
}

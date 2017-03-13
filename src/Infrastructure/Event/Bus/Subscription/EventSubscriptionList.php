<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EventSubscriptionList extends TypedList implements UniqueValueInterface
{
    public function getTransportByName($transport_name)
    {
        foreach ($this->items as $sub) {
            if ($sub->getEventTransport()->getName() === $transport_name) {
                return $sub->getEventTransport();
            }
        }
        return null;
    }

    protected function getItemImplementor()
    {
        return EventSubscriptionInterface::CLASS;
    }
}

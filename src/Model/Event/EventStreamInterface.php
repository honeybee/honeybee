<?php

namespace Honeybee\Model\Event;

use Honeybee\Model\Event\AggregateRootEventInterface;

interface EventStreamInterface
{
    public function push(AggregateRootEventInterface $event);

    public function getIdentifier();

    public function getEvents();
}

<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Object;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Model\Event\AggregateRootEventList;

class EventStream extends Object implements EventStreamInterface
{
    protected $identifier;

    protected $events;

    public function __construct(array $state = array())
    {
        $this->events = new AggregateRootEventList();

        parent::__construct($state);
    }

    public function push(AggregateRootEventInterface $event)
    {
        $this->events->addItem($event);
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getEvents()
    {
        return $this->events;
    }

    protected function setEvents(AggregateRootEventList $events)
    {
        $this->events = $events;
    }
}

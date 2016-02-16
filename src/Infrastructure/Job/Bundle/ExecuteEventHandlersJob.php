<?php

namespace Honeybee\Infrastructure\Job\Bundle;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Job\Job;

class ExecuteEventHandlersJob extends Job
{
    protected $event;

    protected $channel;

    protected $subscription_index;

    /**
     * @hiddenProperty
     */
    protected $event_bus;

    public function __construct(EventBusInterface $event_bus, array $state)
    {
        parent::__construct($state);

        $this->event_bus = $event_bus;
    }

    public function run(array $parameters = [])
    {
        if (!$this->channel) {
            throw new RuntimeError('Missing required channel parameter.');
        }

        $this->event_bus->executeHandlers($this->channel, $this->event, $this->subscription_index);
    }

    protected function setEvent($event_state)
    {
        if (is_array($event_state)) {
            if (!isset($event_state[self::OBJECT_TYPE])) {
                throw new RuntimeError('Unable to create event without type information.');
            }

            $event_implementor = $event_state[self::OBJECT_TYPE];
            if (!class_exists($event_implementor)) {
                throw new RuntimeError('Unable to resolve event implementor: ' . $event_implementor);
            }

            $this->event = new $event_implementor($event_state);
        } elseif ($event_state instanceof EventInterface) {
            $this->event = $event_state;
        } else {
            throw new RuntimeError(
                sprintf(
                    'Invalid event data-type given. Only array and EventInterface are supported.',
                    EventInterface::CLASS
                )
            );
        }
    }
}

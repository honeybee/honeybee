<?php

namespace Honeybee\Infrastructure\Job\Bundle;

use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Job\Job;
use Honeybee\Infrastructure\Job\Strategy\JobStrategy;

class ExecuteEventHandlersJob extends Job
{
    protected $event;

    protected $channel;

    protected $subscription_index;

    /**
     * @hiddenProperty
     */
    protected $event_bus;

    /**
     * @hiddenProperty
     */
    protected $strategy;

    /**
     * @hiddenProperty
     */
    protected $strategy_callback;

    /**
     * @hiddenProperty
     */
    protected $settings;

    public function __construct(
        array $state,
        EventBusInterface $event_bus,
        Closure $strategy_callback,
        SettingsInterface $settings = null
    ) {
        parent::__construct($state);

        $this->event_bus = $event_bus;
        $this->strategy_callback = $strategy_callback;
        $this->settings = $settings ?: new Settings;
    }

    public function run(array $parameters = [])
    {
        if (!$this->channel) {
            throw new RuntimeError('Missing required channel parameter.');
        }

        if ($this->getStrategy()->hasFailed()) {
            throw new RuntimeError('Event is no longer valid according to strategy.');
        }

        $this->event_bus->executeHandlers($this->channel, $this->event, $this->subscription_index);
    }

    public function getStrategy()
    {
        if (!$this->strategy) {
            $this->strategy = $this->createStrategy();
        }
        return $this->strategy;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getIsoDate()
    {
        return $this->event->getIsoDate();
    }

    protected function createStrategy()
    {
        $create_function = $this->strategy_callback;
        $strategy = $create_function($this);

        if (!$strategy instanceof JobStrategy) {
            throw new RuntimeError(
                sprintf(
                    'Invalid strategy type given: %s, expected instance of %s',
                    get_class($strategy),
                    JobStrategy::CLASS
                )
            );
        }

        return $strategy;
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
                    'Invalid event data-type given. Only array and %s are supported.',
                    EventInterface::CLASS
                )
            );
        }
    }
}

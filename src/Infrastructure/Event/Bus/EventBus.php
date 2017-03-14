<?php

namespace Honeybee\Infrastructure\Event\Bus;

use Trellis\Common\Object;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionInterface;
use Honeybee\Infrastructure\Event\Bus\Subscription\EventSubscriptionList;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;

class EventBus extends Object implements EventBusInterface
{
    protected $channel_map;

    protected $logger;

    public function __construct(ChannelMap $channel_map, LoggerInterface $logger)
    {
        $this->channel_map = $channel_map;
        $this->logger = $logger;
    }

    public function executeHandlers($channel_name, EventInterface $event, $subscription_index)
    {
        if (!$this->channel_map->hasKey($channel_name)) {
            throw new RuntimeError(
                sprintf(
                    'Trying to execute event-handlers for unknown channel "%s". Available are: %s',
                    $channel_name,
                    implode(', ', $this->channel_map->getKeys())
                )
            );
        }

        $subscription = $this->getSubscriptions($channel_name)->getItem((int)$subscription_index);
        $is_active = false;
        if ($subscription->isActivated()) {
            foreach ($subscription->getEventFilters() as $filter) {
                if ($filter->accept($event)) {
                    $is_active = true;
                    break;
                }
            }
        }

        if ($is_active) {
            foreach ($subscription->getEventHandlers() as $event_handler) {
                if ($subscription->getSettings()->get('log', false) === true) {
                    $this->logger->debug(
                        sprintf(
                            '[EventBus][ExecHandler][%s][%s][%s] "%s" may handle event "%s"',
                            $channel_name,
                            $subscription_index,
                            $subscription->getEventTransport()->getName(),
                            get_class($event_handler),
                            (string)$event
                        )
                    );
                }
                $event_handler->handleEvent($event);
            }
        }
    }

    public function distribute($channel_name, EventInterface $event)
    {
        if (!$this->channel_map->hasKey($channel_name)) {
            throw new RuntimeError(
                sprintf(
                    'Trying to distribute event over unknown channel "%s". Available are: %s',
                    $channel_name,
                    implode(', ', $this->channel_map->getKeys())
                )
            );
        }
        $subscription_index = 0;
        $this->getSubscriptions($channel_name)->filter(
            function ($subscription) use ($channel_name, $event, &$subscription_index) {
                $is_active = false;
                if ($subscription->isActivated()) {
                    foreach ($subscription->getEventFilters() as $filter) {
                        if ($filter->accept($event)) {
                            $is_active = true;
                            break;
                        }
                    }
                }

                if ($is_active) {
                    if ($subscription->getSettings()->get('log', false) === true) {
                        $this->logger->debug(
                            sprintf(
                                '[EventBus][Distribute][%s][%s][%s] Event "%s"',
                                $channel_name,
                                $subscription_index,
                                $subscription->getEventTransport()->getName(),
                                (string)$event
                            )
                        );
                    }
                    $subscription->getEventTransport()->send(
                        $channel_name,
                        $event,
                        $subscription_index,
                        $subscription->getSettings()
                    );
                }
                $subscription_index++;
            }
        );
    }

    public function subscribe($channel_name, EventSubscriptionInterface $subscription)
    {
        if (!$this->channel_map->hasKey($channel_name)) {
            throw new RuntimeError(
                sprintf(
                    'Trying to subscribe to unknown channel "%s". Available are: %s',
                    $channel_name,
                    implode(', ', $this->channel_map->getKeys())
                )
            );
        }

        $this->getChannel($channel_name)->subscribe($subscription);
    }

    public function unsubscribe($channel_name, EventSubscriptionInterface $subscription)
    {
        if (!$this->channel_map->hasKey($channel_name)) {
            throw new RuntimeError(
                sprintf(
                    'Trying to unsubscribe from unknown channel "%s". Available are: %s',
                    $channel_name,
                    implode(', ', $this->channel_map->getKeys())
                )
            );
        }

        $this->getChannel($channel_name)->unsubscribe($subscription);
    }

    public function getChannels()
    {
        return $this->channel_map;
    }

    public function getChannel($channel_name)
    {
        $channel_map = $this->channel_map;

        return $channel_map->hasKey($channel_name) ? $channel_map->getItem($channel_name) : null;
    }

    public function getSubscriptions($channel_name)
    {
        $channel = $this->getChannel($channel_name);

        return $channel ? $channel->getSubscriptions() : new EventSubscriptionList();
    }
}

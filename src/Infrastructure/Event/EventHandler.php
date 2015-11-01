<?php

namespace Honeybee\Infrastructure\Event;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Psr\Log\LoggerInterface;

abstract class EventHandler implements EventHandlerInterface
{
    protected $config;

    protected $logger;

    public function __construct(ConfigInterface $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function handleEvent(EventInterface $event)
    {
        return $this->invokeEventHandler($event);
    }

    protected function invokeEventHandler(EventInterface $event, $handler_prefix = 'on', array $additional_args = [])
    {
        $namespaced_class_parts = explode('\\', get_class($event));
        $event_class_name = end($namespaced_class_parts);
        $clean_event_name = preg_replace('~Event$~', '', $event_class_name);
        $handler_method = $handler_prefix . ucfirst($clean_event_name);

        if (is_callable([ $this, $handler_method ])) {
            return call_user_func_array([ $this, $handler_method ], array_merge([ $event ], $additional_args));
        } else {
            return false;
        }
    }
}

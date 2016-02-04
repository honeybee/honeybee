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
        $handler_method = $this->lookupHandlerByEventClassName(get_class($event), $handler_prefix);

        if ($handler_method) {
            return call_user_func_array([ $this, $handler_method ], array_merge([ $event ], $additional_args));
        } else {
            return false;
        }
    }

    protected function lookupHandlerByEventClassName($class_name, $handler_prefix)
    {
        $namespaced_class_parts = explode('\\', $class_name);
        $event_class_name = end($namespaced_class_parts);
        $clean_event_name = preg_replace('~Event$~', '', $event_class_name);
        $handler_method = $handler_prefix . ucfirst($clean_event_name);

        if (!is_callable([ $this, $handler_method ])) {
            $parent_class = get_parent_class($class_name);
            if ($parent_class && is_subclass_of($parent_class, EventInterface::CLASS)) {
                return $this->lookupHandlerByEventClassName($parent_class, $handler_prefix);
            }

            return false;
        }

        return $handler_method;
    }
}

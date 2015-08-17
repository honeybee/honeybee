<?php

namespace Honeybee\Infrastructure\Event;

interface EventHandlerInterface
{
    public function handleEvent(EventInterface $event);
}

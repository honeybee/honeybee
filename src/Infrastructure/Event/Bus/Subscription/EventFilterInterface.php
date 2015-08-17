<?php

namespace Honeybee\Infrastructure\Event\Bus\Subscription;

use Honeybee\Infrastructure\Event\EventInterface;

interface EventFilterInterface
{
    public function accept(EventInterface $event);
}

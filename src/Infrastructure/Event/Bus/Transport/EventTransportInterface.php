<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;

interface EventTransportInterface
{
    public function send($channel_name, EventInterface $event);

    public function getName();
}

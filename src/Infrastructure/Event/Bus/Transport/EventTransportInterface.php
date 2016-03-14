<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

interface EventTransportInterface
{
    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null);

    public function getName();
}

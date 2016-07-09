<?php

namespace Honeybee\Infrastructure\Event\Bus\Channel;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ChannelMap extends TypedMap implements UniqueItemInterface
{
    const CHANNEL_DOMAIN = 'honeybee.events.domain';

    const CHANNEL_INFRA = 'honeybee.events.infrastructure';

    const CHANNEL_FILES = 'honeybee.events.files';

    const CHANNEL_REPLAY = 'honeybee.events.replay';

    const CHANNEL_FAILED = 'honeybee.events.failed';

    protected static $default_channels = [
        self::CHANNEL_DOMAIN,
        self::CHANNEL_INFRA,
        self::CHANNEL_FILES,
        self::CHANNEL_REPLAY,
        self::CHANNEL_FAILED
    ];

    public function __construct(array $channels = [])
    {
        parent::__construct(ChannelInterface::CLASS, $channels);
    }

    public static function getDefaultChannels()
    {
        return self::$default_channels;
    }
}

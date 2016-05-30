<?php

namespace Honeybee\Infrastructure\Event\Bus\Channel;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class ChannelMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
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

    protected function getItemImplementor()
    {
        return ChannelInterface::CLASS;
    }

    public static function getDefaultChannels()
    {
        return self::$default_channels;
    }
}

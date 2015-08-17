<?php

namespace Honeybee\Infrastructure\Event\Bus\Channel;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class ChannelMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return ChannelInterface::CLASS;
    }
}

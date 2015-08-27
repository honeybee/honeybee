<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class CommandMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return CommandInterface::CLASS;
    }
}

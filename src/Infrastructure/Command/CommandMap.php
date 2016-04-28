<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class CommandMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return CommandInterface::CLASS;
    }
}

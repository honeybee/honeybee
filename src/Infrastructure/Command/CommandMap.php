<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class CommandMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $commands = [])
    {
        parent::__construct(CommandInterface::CLASS, $commands);
    }
}

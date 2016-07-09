<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class CommandList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $commands = [])
    {
        parent::__construct(CommandInterface::CLASS, $commands);
    }
}

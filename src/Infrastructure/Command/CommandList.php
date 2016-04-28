<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class CommandList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return CommandInterface::CLASS;
    }
}

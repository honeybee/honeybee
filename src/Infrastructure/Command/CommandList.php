<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class CommandList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return CommandInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueCollectionInterface;

class CommandBuilderList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return CommandBuilderInterface::CLASS;
    }
}

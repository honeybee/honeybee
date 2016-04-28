<?php

namespace Honeybee\Model\Command;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EmbeddedEntityTypeCommandList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EmbeddedEntityTypeCommandInterface::CLASS;
    }
}

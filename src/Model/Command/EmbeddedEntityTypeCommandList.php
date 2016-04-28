<?php

namespace Honeybee\Model\Command;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EmbeddedEntityTypeCommandList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EmbeddedEntityTypeCommandInterface::CLASS;
    }
}

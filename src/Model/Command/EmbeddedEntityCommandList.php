<?php

namespace Honeybee\Model\Command;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EmbeddedEntityCommandList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EmbeddedEntityCommandInterface::CLASS;
    }
}

<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedList;

class EmbeddedEntityEventList extends TypedList implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return EmbeddedEntityEventInterface::CLASS;
    }
}

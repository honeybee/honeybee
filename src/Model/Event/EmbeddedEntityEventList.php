<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedList;

class EmbeddedEntityEventList extends TypedList implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return EmbeddedEntityEventInterface::CLASS;
    }
}

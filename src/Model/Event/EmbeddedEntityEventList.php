<?php

namespace Honeybee\Model\Event;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EmbeddedEntityEventList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $embedded_entity_events = [])
    {
        parent::__construct(EmbeddedEntityEventInterface::CLASS, $embedded_entity_events);
    }
}

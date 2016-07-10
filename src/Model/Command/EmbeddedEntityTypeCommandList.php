<?php

namespace Honeybee\Model\Command;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class EmbeddedEntityTypeCommandList extends TypedList implements UniqueItemInterface
{
    public function __construct(array $embedded_entity_type_commands = [])
    {
        parent::__construct(EmbeddedEntityTypeCommandInterface::CLASS, $embedded_entity_type_commands);
    }
}

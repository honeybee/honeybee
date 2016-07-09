<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Collection\TypedList;
use Trellis\Collection\UniqueItemInterface;

class StructureVersionList extends TypedList implements UniqueItemInterface
{
    private $identifier;

    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    protected function getItemImplementor()
    {
        return StructureVersionInterface::CLASS;
    }
}

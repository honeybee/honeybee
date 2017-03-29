<?php

namespace Honeybee\Infrastructure\Migration;

use Trellis\Common\Collection\TypedList;
use Trellis\Common\Collection\UniqueValueInterface;

class StructureVersionList extends TypedList implements UniqueValueInterface
{
    private $identifier;

    public function __construct($identifier, array $items = [])
    {
        $this->identifier = $identifier;
        parent::__construct($items);
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

<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ConnectorMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $connectors = [])
    {
        parent::__construct(ConnectorInterface::CLASS, $connectors);
    }
}

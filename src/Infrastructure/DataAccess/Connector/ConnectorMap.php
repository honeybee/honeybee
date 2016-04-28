<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;

class ConnectorMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return ConnectorInterface::CLASS;
    }
}

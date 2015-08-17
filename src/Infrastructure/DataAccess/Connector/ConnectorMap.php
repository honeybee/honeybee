<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueCollectionInterface;

class ConnectorMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return ConnectorInterface::CLASS;
    }
}

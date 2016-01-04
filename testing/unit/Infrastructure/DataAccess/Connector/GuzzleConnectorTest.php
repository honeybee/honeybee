<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\GuzzleConnector;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Tests\TestCase;

class GuzzleConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name = 'connector', ConfigInterface $config)
    {
        return new GuzzleConnector($name, $config);
    }
}

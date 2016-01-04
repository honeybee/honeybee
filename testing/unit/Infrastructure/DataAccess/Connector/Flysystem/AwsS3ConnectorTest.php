<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector\Flysystem;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Tests\TestCase;

class AwsS3ConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name = 'connector', ConfigInterface $config)
    {
        return new ProxyAwsS3Connector($name, $config);
    }
}

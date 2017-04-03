<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector\Flysystem;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Tests\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;

class AwsS3ConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name, ConfigInterface $config)
    {
        return new ProxyAwsS3Connector($name, $config);
    }
}

<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector\Flysystem;

use Honeybee\Infrastructure\DataAccess\Connector\Flysystem\AwsS3Connector;

class ProxyAwsS3Connector extends AwsS3Connector
{
    protected function connect()
    {
        return new \stdClass;
    }
}

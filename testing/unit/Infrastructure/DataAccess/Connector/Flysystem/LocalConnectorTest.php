<?php

namespace Honeybee\Tests\Infrastructure\DataAccess\Connector\Flysystem;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\ConnectorInterfaceTest;
use Honeybee\Infrastructure\DataAccess\Connector\Flysystem\LocalConnector;
use Honeybee\Tests\TestCase;

class LocalConnectorTest extends ConnectorInterfaceTest
{
    protected function getConnector($name, ConfigInterface $config)
    {
        if (!$config->has('directory')) {
            $settings = $config->toArray();
            $settings['directory'] = __DIR__ . '/Fixture';
            $config = new ArrayConfig($settings);
        }

        return new LocalConnector($name, $config);
    }

    public function testCanReadFromLocalFilesystem()
    {
        $connector = $this->getConnector('local', new ArrayConfig(['directory' => __DIR__ . '/Fixture']));
        $filesystem = $connector->getConnection();
        $this->assertInstanceOf('League\Flysystem\FilesystemInterface', $filesystem);
        $this->assertTrue($filesystem->has('test'), 'Local file should be found');
    }
}

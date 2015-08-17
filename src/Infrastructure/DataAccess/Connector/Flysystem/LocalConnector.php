<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Flysystem;

use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use Honeybee\Common\Error\ConfigError;
use League\Flysystem\Filesystem;
use League\Flysystem\Plugin\EmptyDir;
use League\Flysystem\Plugin\GetWithMetadata;
use League\Flysystem\Plugin\ListFiles;
use League\Flysystem\Plugin\ListPaths;
use League\Flysystem\Plugin\ListWith;

class LocalConnector extends Connector
{
    protected $adapter;

    /**
     * @return Filesystem with a Local adapter
     */
    public function connect()
    {
        $this->needs('directory');

        $this->adapter = new LocalAdapter(
            $this->config->get('directory')
        );

        $this->filesystem = new Filesystem($this->adapter);

        $this->filesystem->addPlugin(new EmptyDir());
        $this->filesystem->addPlugin(new GetWithMetadata());
        $this->filesystem->addPlugin(new ListFiles());
        $this->filesystem->addPlugin(new ListPaths());
        $this->filesystem->addPlugin(new ListWith());

        return $this->filesystem;
    }
}

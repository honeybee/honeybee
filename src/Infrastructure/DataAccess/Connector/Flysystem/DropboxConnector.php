<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Flysystem;

use Dropbox\Client;
use League\Flysystem\Dropbox\DropboxAdapter;
use League\Flysystem\Filesystem;

class DropboxConnector extends AbstractFlysystemConnector
{
    protected $client;
    protected $adapter;
    protected $filesystem;

    /**
     * @return Filesystem with a Dropbox adapter
     */
    protected function connect()
    {
        $this->needs('access_token')->needs('app_id')->needs('path_prefix');

        /*
         * access_token is specific per app_id and should be stored somewhere
         * that is, a dropbox-[connector_name]-app-info(.json) with key/secret is necessary to get the access_token
         *
         * @see https://www.dropbox.com/developers/core/start/php
         * @see https://github.com/dropbox/dropbox-sdk-php/blob/master/lib/Dropbox/AuthInfo.php
         */
        $this->client = new Client(
            $this->config->get('access_token'),
            $this->config->get('app_id')/*,
            $this->config->get('locale'),
            $this->config->get('host')*/
        );

        $this->adapter = new DropboxAdapter($this->client, $this->config->get('path_prefix'));

        $this->filesystem = new Filesystem($this->adapter);

        return $this->filesystem;
    }
}

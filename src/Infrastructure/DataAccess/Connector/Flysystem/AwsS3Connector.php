<?php

namespace Honeybee\Infrastructure\DataAccess\Connector\Flysystem;

use Aws\S3\S3Client;
use Honeybee\Infrastructure\DataAccess\Connector\Connector;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;

class AwsS3Connector extends Connector
{
    protected $client;
    protected $adapter;
    protected $filesystem;

    /**
     * @return Filesystem with a AWS S3 adapter
     */
    public function connect()
    {
        $this->needs('key')
            ->needs('secret')
            ->needs('region', 'Missing setting "region" (e.g. "eu-central-1") in connector "' . $this->name . '".')
            ->needs('bucket');

        $this->client = new S3Client([
            'credentials' => [
                'key'    => $this->config->get('key'),
                'secret' => $this->config->get('secret')
            ],
            'region' => $this->config->get('region'),
            'version' => $this->config->get('version', '2006-03-01'), // 'latest' may be to optimistic
        ]);

        //$result = $this->client->listBuckets();var_dump($result->toArray());die; // poor man's debugging
        $adapter_options = (array)$this->config->get('adapter_options', []);
        $this->adapter = new AwsS3Adapter($this->client, $this->config->get('bucket'), null, $adapter_options);

        $this->filesystem = new Filesystem($this->adapter);

        return $this->filesystem;
    }
}

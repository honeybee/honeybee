<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Elasticsearch\Client;

class ElasticsearchConnector extends Connector
{
    const DEFAULT_PORT = 9200;

    const DEFAULT_HOST = 'localhost';

    /**
     * @return mixed
     */
    public function connect()
    {
        $connection_dsn = sprintf(
            '%s:%d',
            $this->config->get('host', self::DEFAULT_HOST),
            $this->config->get('port', self::DEFAULT_PORT)
        );

        return new Client([ 'hosts' => array($connection_dsn) ]);
    }
}

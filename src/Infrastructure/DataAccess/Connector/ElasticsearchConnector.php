<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Elasticsearch\ClientBuilder;
use Exception;

class ElasticsearchConnector extends Connector
{
    const DEFAULT_PORT = 9200;

    const DEFAULT_HOST = 'localhost';

    /**
     * @return \Elasticsearch\Client
     */
    protected function connect()
    {
        $connection_dsn = sprintf(
            '%s:%d',
            $this->config->get('host', self::DEFAULT_HOST),
            $this->config->get('port', self::DEFAULT_PORT)
        );

        // maybe use building from configuration hash?
        // $client = ClientBuilder::fromConfig($params);

        return (new ClientBuilder())
            ->setHosts([ $connection_dsn ])
            ->build();
    }

    /**
     * Checks connection to elasticsearch. Type of status check can be set via configuration
     * setting 'status_test'. Available status tests are: 'ping' (default), 'info',
     * 'cluster_health', 'cluster_stats' and 'nodes_stats'.
     *
     * @return Status of the connection to the configured elasticsearch
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        // many endpoints are available and suitable for status checks:
        // - GET /
        // - GET _cluster/health?level=indices
        // - GET _cluster/stats
        // - GET _nodes/stats
        // - https://www.elastic.co/guide/en/elasticsearch/guide/2.x/_cat_api.html
        // Here only some are implemented as an example.

        $test = $this->config->get('status_test', 'ping');
        try {
            switch ($test) {
                case 'info':
                    return Status::working($this, $this->getConnection()->info());
                case 'cluster_health':
                    return Status::working($this, $this->getConnection()->cluster()->health());
                case 'cluster_stats':
                    return Status::working($this, $this->getConnection()->cluster()->stats());
                case 'nodes_stats':
                    return Status::working($this, $this->getConnection()->nodes()->stats());
                case 'ping':
                default:
                    if ($this->getConnection()->ping()) {
                        return Status::working($this, [ 'message' => 'Pinging elasticsearch succeeded.' ]);
                    }
                    return Status::failing($this, [ 'message' => 'Pinging elasticsearch failed.' ]);
            }
        } catch (Exception $e) {
            error_log('[' . static::CLASS . '] Error on "' . $test . '": ' . $e->getTraceAsString());
            return Status::failing($this, [ 'message' => 'Error on "' . $test . '": ' . $e->getMessage() ]);
        }
    }
}

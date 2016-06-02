<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;
use GuzzleHttp\Client;
use Exception;

class GuzzleConnector extends Connector
{
    /**
     * @return Client
     */
    protected function connect()
    {
        $base_uri = $this->config->get('base_uri');
        if ($this->config->has('transport') && $this->config->has('host') && $this->config->has('port')) {
            $base_uri = sprintf(
                '%s://%s:%s',
                $this->config->get('transport'),
                $this->config->get('host'),
                $this->config->get('port')
            );
        }

        $client_options = [ 'base_uri' => $base_uri ];

        if ($this->config->get('debug', false)) {
            $client_options['debug'] = true;
        }

        if ($this->config->has('auth')) {
            $auth = $this->config->get('auth');
            if (!isset($auth['username'])) {
                throw new RuntimeError('Missing required "username" setting within given auth config.');
            }
            if (!isset($auth['password'])) {
                throw new RuntimeError('Missing required "password" setting within given auth config.');
            }
            $client_options['auth'] = [ $auth['username'], $auth['password'], $auth['type'] ?: 'basic' ];
        }

        if ($this->config->has('default_headers')) {
            $client_options['headers'] = (array)$this->config->get('default_headers');
        }

        if ($this->config->has('default_options')) {
            $client_options = array_merge($client_options, (array)$this->config->get('default_options')->toArray());
        }

        return new Client($client_options);
    }

    /**
     * Checks connection via HTTP(s).
     *
     * @return Status of the connection to the configured host
     */
    public function getStatus()
    {
        if ($this->config->has('fake_status')) {
            return new Status($this, $this->config->get('fake_status'));
        }

        if (!$this->config->has('status_test')) {
            return Status::unknown($this, [ 'message' => 'No status_test path specified' ]);
        }

        $path = $this->config->get('status_test');
        try {
            $info = $this->config->get('status_verbose', true)
                ? [] // @todo collect info using http://docs.guzzlephp.org/en/latest/request-options.html?#on-stats
                : [];

            $response = $this->getConnection()->get($path);

            if ($status_code >= 200 && $status_code < 300) {
                $info = [
                    'message' => 'GET succeeded: ' . $path
                ];
                if (!empty($info)) {
                    $info['info'] = $info;
                }
                return Status::working($this, $info);
            }

            return Status::failing(
                $this,
                [
                    'message' => 'GET failed: ' . $path,
                    'headers' => $response->getHeaders(),
                    'info' => $info
                ]
            );
        } catch (Exception $e) {
            error_log('[' . static::CLASS . '] Error on "' . $test . '": ' . $e->getTraceAsString());
            return Status::failing($this, [ 'message' => 'Error on "' . $test . '": ' . $e->getMessage() ]);
        }
    }
}

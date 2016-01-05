<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;
use Guzzle\Http\Client;
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

        $request_options = [];

        if ($this->config->has('auth')) {
            $auth = $this->config->get('auth');
            if (!isset($auth['username'])) {
                throw new RuntimeError('Missing required "username" setting within given auth config.');
            }
            if (!isset($auth['password'])) {
                throw new RuntimeError('Missing required "password" setting within given auth config.');
            }
            $request_options['auth'] = [ $auth['username'], $auth['password'], $auth['type'] ?: 'basic' ];
        }

        if ($this->config->has('default_headers')) {
            $request_options['headers'] = (array)$this->config->get('default_headers');
        }

        if ($this->config->has('default_options')) {
            $request_options = array_merge($request_options, (array)$this->config->get('default_options', []));
        }

        $client_options = [];
        if (!empty($request_options)) {
            $client_options['request.options'] = $request_options;
        }

        return new Client($base_uri, $client_options);
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
            $response = $this->getConnection()->get($path)->send();
            if ($response->isSuccessful()) {
                $info = [
                    'message' => 'GET succeeded: ' . $path,
                ];
                if ($this->config->get('status_verbose', true)) {
                    $info['curl_info'] = $response->getInfo();
                }
                return Status::working($this, $info);
            }

            return Status::failing(
                $this,
                [
                    'message' => 'GET failed: ' . $path,
                    'headers' => $response->getRawHeaders(),
                    'curl_info' => $response->getInfo()
                ]
            );
        } catch (Exception $e) {
            error_log('[' . static::CLASS . '] Error on "' . $test . '": ' . $e->getTraceAsString());
            return Status::failing($this, [ 'message' => 'Error on "' . $test . '": ' . $e->getMessage() ]);
        }
    }
}

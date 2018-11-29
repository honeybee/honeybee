<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\TransferStats;
use GuzzleRetry\GuzzleRetryMiddleware;
use Psr\Http\Message\RequestInterface;

class GuzzleRetryConnector extends Connector
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

        $client_options = [
            'base_uri' => $base_uri,
            'retry_enabled' => true,
            'max_retry_attempts' => 2,
            'retry_only_if_retry_after_header' => false,
            'retry_on_status' => [429, 503],
            'default_retry_multiplier' => 1.5, // could be a callback
            'retry_on_timeout' => false, // retry on guzzle 'timeout' or 'connect_timeout'?
            'expose_retry_header' => false,
        ];

        if ($this->config->get('debug', false)) {
            $client_options['debug'] = true;
        }

        if ($this->config->has('auth')) {
            $auth = (array)$this->config->get('auth');
            if (!empty($auth['username']) && !empty($auth['password'])) {
                $client_options['auth'] = [
                    $auth['username'],
                    $auth['password'],
                    isset($auth['type']) ? $auth['type'] : 'basic'
                ];
            }
        }

        if ($this->config->has('default_headers')) {
            $client_options['headers'] = (array)$this->config->get('default_headers');
        }

        if ($this->config->has('default_options')) {
            $client_options = array_merge($client_options, (array)$this->config->get('default_options')->toArray());
        }

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory());

        if ($this->config->has('default_query')) {
            $stack->push(Middleware::mapRequest(
                function (RequestInterface $request) {
                    $uri = $request->getUri();
                    foreach ((array)$this->config->get('default_query')->toArray() as $param => $value) {
                        $uri = Uri::withQueryValue($uri, $param, $value);
                    }
                    return $request->withUri($uri);
                }
            ));
        }

        $client_options['handler'] = $stack;

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
            $info = [];
            $verbose = $this->config->get('status_verbose', true);

            $response = $this->getConnection()->get($path, [
                'retry_enabled' => $this->config->get('status_test_retry_enabled', false),
                'on_stats' => function (TransferStats $stats) use (&$info, $verbose) {
                    if (!$verbose) {
                        return;
                    }
                    $info['effective_uri'] = (string)$stats->getEffectiveUri();
                    $info['transfer_time'] = $stats->getTransferTime();
                    $info = array_merge($info, $stats->getHandlerStats());
                    if ($stats->hasResponse()) {
                        $info['status_code'] = $stats->getResponse()->getStatusCode();
                    } else {
                        $error_data = $stats->getHandlerErrorData();
                        if (is_array($error_data) || is_string($error_data)) {
                            $info['handler_error_data'] = $error_data;
                        }
                    }
                }
            ]);

            $status_code = $response->getStatusCode();
            if ($status_code >= 200 && $status_code < 300) {
                $msg['message'] = 'GET succeeded: ' . $path;
                if (!empty($info)) {
                    $msg['info'] = $info;
                }
                return Status::working($this, $msg);
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
            error_log(
                '[' . static::CLASS . '] Error on "' . $path . '": ' . $e->getMessage() . "\n" . $e->getTraceAsString()
            );
            return Status::failing($this, [ 'message' => 'Error on "' . $path . '": ' . $e->getMessage() ]);
        }
    }
}

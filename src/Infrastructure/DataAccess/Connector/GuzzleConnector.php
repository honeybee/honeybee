<?php

namespace Honeybee\Infrastructure\DataAccess\Connector;

use Honeybee\Common\Error\RuntimeError;
use Guzzle\Http\Client;
use Guzzle\Common\Event;
use Guzzle\Plugin\Log\LogPlugin;

class GuzzleConnector extends Connector
{
    public function connect()
    {
        $base_uri = null;
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

$time = microtime(true);
        $client = new Client($base_uri, $client_options);
$log_plugin = LogPlugin::getDebugPlugin(false);
$client->addSubscriber($log_plugin);
$client->getEventDispatcher()->addListener('client.create_request', function (Event $e) {
    error_log('Client object: ' . spl_object_hash($e['client']));
    error_log("Request object: {$e['request']}");
});
$now = microtime(true);
error_log('GuzzleConnector new client creation: ' . $base_uri . ' ' . round(($now - $time) * 1000, 1) . 'ms');
        return $client;
    }
}

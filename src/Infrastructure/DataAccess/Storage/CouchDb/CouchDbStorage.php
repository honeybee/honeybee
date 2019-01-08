<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb;

use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Honeybee\Common\Error\RuntimeError;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

abstract class CouchDbStorage extends Storage
{
    const METHOD_POST = 'post';

    const METHOD_PUT = 'put';

    const METHOD_GET = 'get';

    const METHOD_DELETE = 'delete';

    protected function request($identifier, $method, array $body = [], array $params = [])
    {
        $allowed_methods = [ self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE ];
        if (!in_array($method, $allowed_methods)) {
            throw new RuntimeError(
                sprintf("Invalid method %s given. Expecting one of: %s", $method, implode(', ', $allowed_methods))
            );
        }

        if (isset($body['revision'])) {
            $params['rev'] = $body['revision'];
        }

        try {
            $client = $this->connector->getConnection();
            $request_path = $this->buildRequestUrl($identifier, $params);
            if (empty($body)) {
                $request = new Request($method, $request_path, [ 'Accept' => 'application/json' ]);
            } else {
                $request = new Request(
                    $method,
                    $request_path,
                    [ 'Accept' => 'application/json', 'Content-Type' => 'application/json' ],
                    json_encode($body)
                );
            }
        } catch (GuzzleException $guzzle_error) {
            throw new RuntimeError(
                sprintf('Failed to %s build request: %s', $method, $guzzle_error),
                0,
                $guzzle_error
            );
        }

        return $client->send($request);
    }

    protected function buildRequestUrl($identifier, array $params = [])
    {
        $request_path = '/' . $this->getDatabase() . '/' . $identifier;

        if (!empty($params)) {
            $request_path .= '?' . http_build_query($params);
        }

        return str_replace('//', '/', $request_path);
    }

    protected function getDatabase()
    {
        $fallback_index = $this->connector->getConfig()->get('database');

        return $this->config->get('database', $fallback_index);
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb;

use Honeybee\Infrastructure\DataAccess\Storage\Storage;
use Honeybee\Infrastructure\DataAccess\Storage\IStorageKey;
use Honeybee\Common\Error\RuntimeError;
use Guzzle\Common\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

abstract class CouchDbStorage extends Storage
{
    const METHOD_POST = 'post';

    const METHOD_PUT = 'put';

    const METHOD_GET = 'get';

    const METHOD_DELETE = 'delete';

    protected function buildRequestFor($identifier, $method, array $body = array(), array $params = array())
    {
        $allowed_methods = [ self::METHOD_GET, self::METHOD_POST, self::METHOD_PUT, self::METHOD_DELETE ];
        if (!in_array($method, $allowed_methods)) {
            throw new RuntimeError(
                sprintf("Invalid method %s given. Expecting one of: ", $method, implode(', ', $allowed_methods))
            );
        }

        $options = [];
        if (!empty($params)) {
            $options['query'] = $params;
        }
        if ($this->config->get('debug', false)) {
            $options['debug'] = true;
        }

        try {
            $client = $this->connector->getConnection();
            $request_path = $this->buildRequestUrl($identifier, $body);
            if ($method === self::METHOD_GET) {
                $request = $client->get($request_path, [], $options);
            } else {
                $request = $client->$method($request_path, [], json_encode($body), $options);
            }
        } catch (GuzzleException $guzzle_error) {
            throw new RuntimeError(
                sprintf("Failed to %s build request: %s", $method, $guzzle_error),
                0,
                $guzzle_error
            );
        }

        return $request;
    }

    protected function buildRequestUrl($identifier, array $params = [])
    {
        $request_path = '/' . $this->getDatabase() . '/';
        $request_path .= $identifier;

        if (isset($params['revision'])) {
            $request_path .= '?rev=' . $params['revision'];
        }

        return $request_path;
    }

    protected function getDatabase()
    {
        $fallback_index = $this->connector->getConfig()->get('database');

        return $this->config->get('database', $fallback_index);
    }
}

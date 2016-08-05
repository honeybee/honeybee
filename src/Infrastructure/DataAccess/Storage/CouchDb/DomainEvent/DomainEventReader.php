<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\DomainEvent;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use GuzzleHttp\Exception\RequestException;

class DomainEventReader extends CouchDbStorage implements StorageReaderInterface
{
    protected $last_key = null;

    public function read($identifier, SettingsInterface $settings = null)
    {
        try {
            $path = sprintf('/%s', $identifier);
            $response = $this->request($path, self::METHOD_GET);
            $result_data = json_decode($response->getBody(), true);
        } catch (RequestException $error) {
            if ($error->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                throw $error;
            }
        }

        return $this->createDomainEvent($result_data['doc']);
    }

    public function readAll(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        if ($settings->get('first', true)) {
            $this->last_key = null;
        }

        $view_params = [
            'include_docs' => 'true',
            'reduce' => 'false',
            'limit' => $this->config->get('limit', 100)
        ];

        if ($this->last_key) {
            $view_params['skip'] = 1;
            $view_params['startkey'] = sprintf('"%s"', $this->last_key);
        }

        if (!$this->config->has('design_doc')) {
            throw new RuntimeError(
                'Missing setting for "design_doc" that holds the name of the couchdb design document, ' .
                'that is expected to contain the event_stream view.'
            );
        }
        $view_path = sprintf(
            '/_design/%s/_view/%s',
            $this->config->get('design_doc'),
            $this->config->get('view_name', 'events_by_timestamp')
        );
        $response = $this->request($view_path, self::METHOD_GET, [], $view_params);
        $result_data = json_decode($response->getBody(), true);

        $events = [];
        foreach ($result_data['rows'] as $event_data) {
            $events[] = $this->createDomainEvent($event_data['doc']);
            $this->last_key = $event_data['doc']['iso_date'];
        }

        return $events;
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createDomainEvent(array $event_data)
    {
        if (!isset($event_data[self::OBJECT_TYPE])) {
            throw new RuntimeError('Missing type key within event data.');
        }

        return new $event_data[self::OBJECT_TYPE]($event_data);
    }
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\EventStream;

use GuzzleHttp\Exception\BadResponseException;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\EventStream;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderIterator;

class EventStreamReader extends CouchDbStorage implements StorageReaderInterface
{
    const STARTKEY_FILTER = '["%s", {}]';

    const ENDKEY_FILTER = '["%s", 1]';

    protected $next_identifier = null;

    protected $identifier_list;

    public function read($identifier, SettingsInterface $settings = null)
    {
        try {
            $view_params = [
                'startkey' => sprintf(self::STARTKEY_FILTER, $identifier),
                'endkey' => sprintf(self::ENDKEY_FILTER, $identifier),
                'include_docs' => 'true',
                'reduce' => 'false',
                'descending' => 'true',
                'limit' => 1000 // @todo use snapshot size config setting as soon as available
            ];
            if (!$this->config->has('design_doc')) {
                throw new RuntimeError(
                    'Missing setting for "design_doc" that holds the name of the couchdb design document, ' .
                    'that is expected to contain the event_stream view.'
                );
            }
            $view_path = sprintf(
                '/_design/%s/_view/%s',
                $this->config->get('design_doc'),
                $this->config->get('view_name', 'event_stream')
            );
            $response = $this->buildRequestFor($view_path, self::METHOD_GET, [], $view_params)->send();
            $result_data = json_decode($response->getBody(), true);
        } catch (BadResponseException $error) {
            if ($error->getResponse()->getStatusCode() === 404) {
                return null;
            } else {
                throw $error;
            }
        }

        if ($result_data['total_rows'] > 0) {
            return $this->createEventStream($identifier, array_reverse($result_data['rows']));
        }

        return null;
    }

    public function readAll(SettingsInterface $settings)
    {
        if ($settings->get('first', true)) {
            $this->identifier_list = $this->fetchEventStreamIdentifiers();
        }
        $this->next_identifier = key($this->identifier_list);
        next($this->identifier_list);

        if (!$this->next_identifier) {
            return [];
        }

        return [ $this->read($this->next_identifier, $settings) ];
    }

    public function getIterator()
    {
        return new StorageReaderIterator($this);
    }

    protected function createEventStream($identifier, array $event_stream_data)
    {
        $events = new AggregateRootEventList;
        foreach ($event_stream_data as $event_data) {
            $event_data = $event_data['doc'];
            if (!isset($event_data[self::OBJECT_TYPE])) {
                throw new RuntimeError("Missing type key within event data.");
            }
            $event_class = $event_data[self::OBJECT_TYPE];
            $events->addItem(new $event_class($event_data));
        }
        $data['identifier'] = $identifier;
        $data['events'] = $events;

        return new EventStream($data);
    }

    protected function fetchEventStreamIdentifiers()
    {
        $event_stream_keys = [];
        $view_name = sprintf('/_design/default_views/_view/%s', $this->config->get('view_name'));

        $request_params = [
            'group' => 'true',
            'group_level' => 1,
            'reduce' => 'true'
        ];

        $response = $this->buildRequestFor(
            sprintf('/_design/default_views/_view/%s', $this->config->get('view_name')),
            self::METHOD_GET,
            [],
            $request_params
        )->send();
        $result_data = json_decode($response->getBody(), true);

        foreach ($result_data['rows'] as $row) {
            $event_stream_keys[$row['key'][0]] = $row['value'];
        }

        return $event_stream_keys;
    }
}

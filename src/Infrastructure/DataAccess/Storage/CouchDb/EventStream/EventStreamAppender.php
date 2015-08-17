<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\CouchDb\EventStream;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\CouchDb\CouchDbStorage;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;

class EventStreamAppender extends CouchDbStorage implements StorageWriterInterface
{
    public function write($domain_event, SettingsInterface $settings = null)
    {
        if (!$domain_event instanceof AggregateRootEventInterface) {
            throw new RuntimeError(
                sprintf(
                    'Invalid payload given to %s, expected type of %s',
                    __METHOD__,
                    AggregateRootEventInterface::CLASS
                )
            );
        }

        $data = $domain_event->toArray();
        $identifier = sprintf('%s-%s', $domain_event->getAggregateRootIdentifier(), $domain_event->getSeqNumber());
        $response_data = $this->buildRequestFor($identifier, self::METHOD_PUT, $data)->send()->json();

        if (!isset($response_data['ok']) || !isset($response_data['rev'])) {
            throw new RuntimeError("Failed to write data.");
        }
    }

    public function delete($identifier, SettingsInterface $settings = null)
    {
        throw new RuntimeError("Deleting domain events from the stream is not allowed!");
    }
}

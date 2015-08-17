<?php

namespace Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\DomainEvent;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Event\AggregateRootEventInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Storage\Elasticsearch\ElasticsearchStorageWriter;

class DomainEventWriter extends ElasticsearchStorageWriter
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

        $this->writeData($domain_event->getUuid(), $domain_event->toArray(), $settings);
    }
}

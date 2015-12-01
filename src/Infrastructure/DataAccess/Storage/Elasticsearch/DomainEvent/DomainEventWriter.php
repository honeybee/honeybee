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

$time = microtime(true);
        $this->writeData($domain_event->getUuid(), $domain_event->toArray(), $settings);
$now = microtime(true);
error_log('Elasticsearch DomainEventWriter::write w/ toArray ' . $domain_event->getUuid() . ': ' . round(($now - $time) * 1000, 1) . 'ms');
    }
}

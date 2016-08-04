<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\DomainEvent;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\ElasticsearchFinder;
use Honeybee\Model\Event\AggregateRootEventInterface;

class DomainEventFinder extends ElasticsearchFinder
{
    protected function mapResultData(array $result_data)
    {
        $results = [];
        $hits = $result_data['hits'];
        foreach ($hits['hits'] as $hit) {
            $event_data = $hit['_source'];
            $event_type = isset($event_data[self::OBJECT_TYPE]) ? $event_data[self::OBJECT_TYPE] : false;
            if (!$event_type || !class_exists($event_type, true)) {
                throw new RuntimeError('Invalid or corrupt type information within event data.');
            }
            unset($event_data[self::OBJECT_TYPE]);

            $domain_event = new $event_type($event_data);
            if (!$domain_event instanceof AggregateRootEventInterface) {
                throw new RuntimeError(
                    sprintf(
                        'Non-event object given within result data. %s only supports instances of %s.',
                        __CLASS__,
                        AggregateRootEventInterface::CLASS
                    )
                );
            }
            $results[] = $domain_event;
        }

        return $results;
    }
}

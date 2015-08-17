<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Event\AggregateRootCommittedEvent;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\EventHandler;
use Honeybee\Infrastructure\Filesystem\FilesystemServiceInterface;
use Psr\Log\LoggerInterface;

class DomainEventFileStoreUpdater extends EventHandler
{
    protected $filesystem_service;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        AggregateRootTypeMap $artm,
        FilesystemServiceInterface $filesystem_service
    ) {
        parent::__construct($config, $logger);

        $this->aggregate_root_type_map = $artm;
        $this->filesystem_service = $filesystem_service;
    }

    protected function onAggregateRootCommitted(AggregateRootCommittedEvent $event)
    {
        $art_fqdn = $event->getAggregateRootType();

        // $this->aggregate_root_type_map->getByFqdn($art_fqdn);
        $art = new $art_fqdn();

        $event_data = $event->getData();
        $this->logger->debug(
            '[{method}] Incoming event {event} with data: {data}',
            [
                'method' => __METHOD__,
                'event' => get_class($event),
                'data' => $event_data
            ]
        );

        $file_uri = $this->filesystem_service->createUri($art);

        $this->logger->debug('Example file URI to store a file under: {uri}', [ 'uri' => $file_uri ]);
    }
}

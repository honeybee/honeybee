<?php

namespace Honeybee\Infrastructure\DataAccess\UnitOfWork;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Aggregate\AggregateRootInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Honeybee\Model\Event\AggregateRootEventList;
use Honeybee\Model\Event\AggregateRootEventListMap;
use Honeybee\Model\Event\EventStream;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageReaderInterface;
use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterInterface;
use Psr\Log\LoggerInterface;
use SplObjectStorage;

/**
 * The UnitOfWork tracks changes and provides persistence of changes done to aggregate-roots.
 */
class UnitOfWork implements UnitOfWorkInterface
{
    /**
     * @var ConfigInterface $config
     */
    protected $config;

    /**
     * @var AggregateRootTypeInterface $aggregate_root_type
     */
    protected $aggregate_root_type;

    /**
     * @var StorageReaderInterface $event_reader
     */
    protected $event_reader;

    /**
     * @var StorageWriterInterface $event_writer
     */
    protected $event_writer;

    /**
     * Maps an aggregate-root to it's event-stream.Holds all aggregate roots being tracking at certain point of time.
     *
     * @var SplObjectStorage $tracked_aggregate_roots
     */
    protected $tracked_aggregate_roots;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Create a new UnitOfWork instance for the given aggregate_root_type.
     *
     * @param ConfigInterface $config,
     * @param AggregateRootTypeInterface $aggregate_root_type
     * @param StorageReaderInterface $event_reader
     * @param StorageWriterInterface $event_writer
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        AggregateRootTypeInterface $aggregate_root_type,
        StorageReaderInterface $event_reader,
        StorageWriterInterface $event_writer,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->aggregate_root_type = $aggregate_root_type;
        $this->event_reader = $event_reader;
        $this->event_writer = $event_writer;
        $this->logger = $logger;

        $this->tracked_aggregate_roots = new SplObjectStorage();
    }

    /**
     * Create a fresh aggregate-root instance.
     *
     * @return AggregateRootInterface
     */
    public function create()
    {
        $aggregate_root = $this->aggregate_root_type->createEntity();
        $this->startTracking($aggregate_root);

        return $aggregate_root;
    }

    /**
     * Checkout the aggregate-root for a given identifier.
     *
     * @param string $aggregate_root_id
     *
     * @return AggregateRootInterface
     */
    public function checkout($aggregate_root_id)
    {
        $aggregate_root = $this->create();
        $event_stream = $this->event_reader->read($aggregate_root_id);

        if ($event_stream) {
            $aggregate_root->reconstituteFrom($event_stream->getEvents());
        } else {
            $this->logger->debug(__METHOD__ . ' - ' . get_class($this->event_reader));
            throw new RuntimeError(
                sprintf('Unable to load event stream for given AR identifier: %s', $aggregate_root_id)
            );
        }
        return $aggregate_root;
    }

    /**
     * Commit all changes that are pending for our tracked aggregate-roots.
     *
     * @return AggregateRootEventList Returns a list of events that were actually committed.
     */
    public function commit()
    {
        $committed_events_map = new AggregateRootEventListMap();
        $comitted_ars = [];
        foreach ($this->tracked_aggregate_roots as $aggregate_root) {
            $event_stream = $this->tracked_aggregate_roots[$aggregate_root];
            $committed_events_list = new AggregateRootEventList();
            foreach ($aggregate_root->getUncomittedEvents() as $uncomitted_event) {
                $event_stream->push($uncomitted_event);
                $this->event_writer->write($uncomitted_event);
                $committed_events_list->push($uncomitted_event);
            }
            $aggregate_root->markAsComitted();
            $committed_events_map->setItem($aggregate_root->getIdentifier(), $committed_events_list);
            $comitted_ars[] = $aggregate_root;
        }

        foreach ($comitted_ars as $comitted_ar) {
            $this->tracked_aggregate_roots->offsetUnset($aggregate_root);
        }

        return $committed_events_map;
    }

    public function rollback()
    {
        // @todo implement
    }

    /**
     * Register the given aggregate-root to our event stream map.
     *
     * @param AggregateRootInterface $aggregate_root
     */
    protected function startTracking(AggregateRootInterface $aggregate_root)
    {
        if ($this->tracked_aggregate_roots->contains($aggregate_root)) {
            throw new RuntimeError("Trying to checkout aggregate that already has a session open.");
        }

        $aggregate_root_id = $aggregate_root->getIdentifier();
        $this->tracked_aggregate_roots[$aggregate_root] = new EventStream([ 'identifier' => $aggregate_root_id ]);
    }
}

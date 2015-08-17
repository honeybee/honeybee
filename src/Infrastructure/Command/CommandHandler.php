<?php

namespace Honeybee\Infrastructure\Command;

use Trellis\Common\Object;
use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Event\EventList;
use Psr\Log\LoggerInterface;
use Trellis\Common\Collection\ArrayList;

abstract class CommandHandler extends Object implements CommandHandlerInterface
{
    /**
     * @var EventBusInterface $event_bus
     */
    protected $event_bus;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * Process the given command and return a list of events that reflects the state-delta of interest.
     *
     * @param CommandInterface $command
     * @param int $retry_count
     *
     * @return EventList List of resulting events
     */
    abstract protected function tryToExecute(CommandInterface $command, $retry_count = 0);

    /**
     * Return the name of the event-channel used to post events to subscribers.
     *
     * @return string
     */
    abstract protected function getEventChannelName();

    /**
     * Creates a new CommandHandler instance.
     *
     * @var EventBusInterface $event_bus
     * @var LoggerInterface $logger
     */
    public function __construct(
        EventBusInterface $event_bus,
        LoggerInterface $logger
    ) {
        $this->event_bus = $event_bus;
        $this->logger = $logger;
    }

    /**
     * Executes the given command and post any resulting events to the event-bus.
     *
     * @param CommandInterface $command
     */
    public function execute(CommandInterface $command)
    {
        $this->logger->debug('Executing command "{command}".', [ 'command' => get_class($command) ]);
        // @todo allow providing settings within the commands.xml and use them here
        $max_retries = 3;
        $retry_timeout = 1000;
        $retry_count = 0;
        $events = new EventList();

        while ($retry_count <= $max_retries) {
            try {
                $events = $this->tryToExecute($command, $retry_count);
                break;
            } catch (Exception $conflict) {
                // TODO introduce DataAccess(Conflict)Error or similar and throw it from the storage etc, classes?
                if ($retry_count === $max_retries) {
                    throw $conflict;
                } else {
                    $retry_count++;
                    usleep($retry_timeout);
                    $this->logger->error(static::class . ' ~ ' . $conflict->getMessage());
                }
            }
        }

        if (!$events instanceof EventList) {
            throw new RuntimeError(
                sprintf(
                    'Unexpected type returned from call to tryExecute. Expecting typeof %s, but given %s. ',
                    EventList::CLASS,
                    get_class($events)
                )
            );
        }

        foreach ($events as $event) {
            $this->event_bus->distribute($this->getEventChannelName(), $event);
        }
    }
}

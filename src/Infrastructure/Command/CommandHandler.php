<?php

namespace Honeybee\Infrastructure\Command;

use Exception;
use Psr\Log\LoggerInterface;
use Trellis\Common\Object;

abstract class CommandHandler extends Object implements CommandHandlerInterface
{
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
     * Creates a new CommandHandler instance.
     *
     * @var LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Executes the given command and post any resulting events to the event-bus.
     *
     * @param CommandInterface $command
     */
    public function execute(CommandInterface $command)
    {
        // @todo allow providing settings within the commands.xml and use them here
        $max_retries = 3;
        $retry_timeout = 100000;
        $retry_count = 0;
        $done = false;

        while (!$done && $retry_count <= $max_retries) {
            try {
                $this->tryToExecute($command, $retry_count);
                $done = true;
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
    }
}

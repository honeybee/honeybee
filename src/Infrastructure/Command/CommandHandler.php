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
        $this->tryToExecute($command, 0);
    }
}

<?php

namespace Honeybee\Infrastructure\Event;

use Honeybee\Common\Error\RuntimeError;

class FailedJobEventHandler extends EventHandler
{
    public function handleEvent(EventInterface $event)
    {
        if (!$event instanceof FailedJobEvent) {
            throw new RuntimeError(sprintf('Unexpected failed job event type "%s"', get_class($event)));
        }

        $this->logger->error(
            "[{method}] The following message failed to be handled:\n{trace}",
            [
                'method' => __METHOD__,
                'trace' => (string)$event
            ]
        );

        return true;
    }
}

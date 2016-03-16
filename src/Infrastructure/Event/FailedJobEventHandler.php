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

        //@todo improve trace output
        $this->logger->error(
            "[{method}] The following message failed to be handled:\n[Job]\n{state}\n[Error]\n{error}",
            [
                'method' => __METHOD__,
                'state' => print_r($event->getFailedJobState(), true),
                'error' => print_r($event->getMetaData(), true)
            ]
        );

        return true;
    }
}

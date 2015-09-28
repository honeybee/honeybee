<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Event\Event;

class ProcessCompletedEvent extends Event
{
    protected $process_state;

    public function __construct(ProcessStateInterface $process_state)
    {
        $this->process_state = $process_state;
    }

    public function getProcessState()
    {
        return $this->process_state;
    }

    public function getType()
    {
        return 'honeybee.infrastructure.process_completed';
    }
}

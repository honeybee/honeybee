<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Event\Event;

class ProcessCompletedEvent extends Event
{
    protected $process_state;

    protected function setProcessState($process_state)
    {
        if ($process_state instanceof ProcessStateInterface) {
            $this->process_state = $process_state;
        } elseif (is_array($process_state)) {
            $this->process_state = new ProcessState($process_state);
        } else {
            throw new RuntimeError('Invalid value given for process-state given to process-complete event.');
        }
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

<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Event\EventInterface;

interface ProcessManagerInterface
{
    public function beginProcess(ProcessStateInterface $process_state);

    public function continueProcess(EventInterface $event);
}

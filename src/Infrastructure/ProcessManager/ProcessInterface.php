<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Infrastructure\Event\EventInterface;
use Workflux\StateMachine\StateMachine;

interface ProcessInterface
{
    public function getName();

    public function proceed(ProcessStateInterface $process_state, EventInterface $event = null);

    public function hasFinished(ProcessStateInterface $process_state);

    public function hasStarted(ProcessStateInterface $process_state);

    public function getStateMachine();
}

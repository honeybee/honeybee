<?php

namespace Honeybee\Infrastructure\Saga;

use Workflux\StateMachine\StateMachine;

interface SagaInterface
{
    public function getName($name);

    public function proceed(SagaSubjectInterface $saga_subject, $event_name = null);

    public function hasFinished(SagaSubjectInterface $saga_subject);

    public function hasStarted(SagaSubjectInterface $saga_subject);

    public function getStateMachine();
}

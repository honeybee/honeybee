<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Infrastructure\Event\EventInterface;

interface SagaServiceInterface
{
    public function beginSaga(SagaSubjectInterface $saga_subject);

    public function continueSaga(EventInterface $event);
}

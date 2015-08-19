<?php

namespace Honeybee\Infrastructure\Saga;

use Trellis\Common\ObjectInterface;

interface SagaSubjectInterface extends ObjectInterface
{
    public function getUuid();

    public function getPayload();

    public function getSagaName();

    public function getStateName();
}

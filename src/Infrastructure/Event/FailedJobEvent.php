<?php

namespace Honeybee\Infrastructure\Event;

use Assert\Assertion;
use Ramsey\Uuid\Uuid;

class FailedJobEvent extends Event
{
    protected $failed_job_state;

    public function __construct(array $state = [])
    {
        $state['uuid'] = Uuid::uuid4()->toString();

        parent::__construct($state);
    }

    public function getFailedJobState()
    {
        return $this->failed_job_state;
    }

    public function getType()
    {
        return $this->failed_job_state['event']['@type'] . '.failed';
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();
        Assertion::isArray($this->failed_job_state);
    }
}

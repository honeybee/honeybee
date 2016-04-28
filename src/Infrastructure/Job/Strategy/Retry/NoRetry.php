<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use Honeybee\Infrastructure\Job\Strategy\Failure\FailureStrategyInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class NoRetry implements RetryStrategyInterface, FailureStrategyInterface
{
    protected $job;

    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    public function getInterval()
    {
        return false;
    }

    public function hasFailed()
    {
        $metadata = $this->job->getMetadata();
        return isset($metadata['retries']);
    }
}

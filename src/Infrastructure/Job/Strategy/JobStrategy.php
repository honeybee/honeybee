<?php

namespace Honeybee\Infrastructure\Job\Strategy;

use Honeybee\Infrastructure\Job\Strategy\Retry\RetryStrategyInterface;
use Honeybee\Infrastructure\Job\Strategy\Failure\FailureStrategyInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class JobStrategy
{
    protected $retry_strategy;

    protected $failure_strategy;

    public function __construct(
        RetryStrategyInterface $retry_strategy,
        FailureStrategyInterface $failure_strategy
    ) {
        $this->retry_strategy = $retry_strategy;
        $this->failure_strategy = $failure_strategy;
    }

    public function getRetryInterval(JobInterface $job)
    {
        return $this->retry_strategy->getInterval($job);
    }

    public function hasFailed(JobInterface $job)
    {
        return $this->failure_strategy->hasFailed($job);
    }
}

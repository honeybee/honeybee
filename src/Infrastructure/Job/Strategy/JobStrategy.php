<?php

namespace Honeybee\Infrastructure\Job\Strategy;

use Honeybee\Infrastructure\Job\Strategy\Retry\RetryStrategyInterface;
use Honeybee\Infrastructure\Job\Strategy\Failure\FailureStrategyInterface;

class JobStrategy
{
    protected $retry_strategy;

    protected $failure_strategy;

    public function __construct(RetryStrategyInterface $retry_strategy, FailureStrategyInterface $failure_strategy)
    {
        $this->retry_strategy = $retry_strategy;
        $this->failure_strategy = $failure_strategy;
    }

    public function getRetryInterval()
    {
        return $this->retry_strategy->getInterval();
    }

    public function hasFailed()
    {
        return $this->failure_strategy->hasFailed();
    }

    public function canRetry()
    {
        return !$this->hasFailed() && $this->getRetryInterval();
    }
}

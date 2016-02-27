<?php

namespace Honeybee\Infrastructure\Event\Bus\Strategy;

use Honeybee\Infrastructure\Event\Bus\Strategy\Retry\RetryStrategyInterface;
use Honeybee\Infrastructure\Event\Bus\Strategy\Failure\FailureStrategyInterface;

class EventStrategy
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

    public function getRetryStrategy()
    {
        return $this->retry_strategy;
    }

    public function getFailureStrategy()
    {
        return $this->failure_strategy;
    }
}

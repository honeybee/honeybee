<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use Honeybee\Infrastructure\Job\Strategy\Retry\RetryStrategyInterface;
use Honeybee\Infrastructure\Job\Strategy\Failure\FailureStrategyInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class NoRetry implements RetryStrategyInterface, FailureStrategyInterface
{
    public function getInterval(JobInterface $job)
    {
        return false;
    }

    public function hasFailed(JobInterface $job)
    {
        $meta_data = $job->getMetaData();
        return isset($meta_data['retries']);
    }
}

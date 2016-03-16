<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use Honeybee\Infrastructure\Job\JobInterface;

interface RetryStrategyInterface
{
    public function getInterval(JobInterface $job);
}

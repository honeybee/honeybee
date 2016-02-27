<?php

namespace Honeybee\Infrastructure\Event\Bus\Strategy\Retry;

use Honeybee\Infrastructure\Job\JobInterface;

interface RetryStrategyInterface
{
    public function getInterval(JobInterface $job);
}

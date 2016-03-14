<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use Honeybee\Infrastructure\Job\JobInterface;

interface FailureStrategyInterface
{
    public function hasFailed(JobInterface $job);
}

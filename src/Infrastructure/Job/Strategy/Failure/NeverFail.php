<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use Honeybee\Infrastructure\Job\JobInterface;

class NeverFail implements FailureStrategyInterface
{
    public function hasFailed(JobInterface $job)
    {
        return false;
    }
}

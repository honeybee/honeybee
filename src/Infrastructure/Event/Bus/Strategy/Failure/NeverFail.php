<?php

namespace Honeybee\Infrastructure\Event\Bus\Strategy\Failure;

use Honeybee\Infrastructure\Job\JobInterface;

class NeverFail implements FailureStrategyInterface
{
    public function hasFailed(JobInterface $job)
    {
        return false;
    }
}

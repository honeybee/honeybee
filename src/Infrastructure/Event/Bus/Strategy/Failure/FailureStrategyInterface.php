<?php

namespace Honeybee\Infrastructure\Event\Bus\Strategy\Failure;

use Honeybee\Infrastructure\Job\JobInterface;

interface FailureStrategyInterface
{
    public function hasFailed(JobInterface $job);
}

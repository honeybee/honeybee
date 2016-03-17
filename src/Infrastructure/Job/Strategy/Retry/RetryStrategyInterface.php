<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

interface RetryStrategyInterface
{
    public function getInterval();
}

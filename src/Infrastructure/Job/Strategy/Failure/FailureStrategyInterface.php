<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

interface FailureStrategyInterface
{
    public function hasFailed();
}

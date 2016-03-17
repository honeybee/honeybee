<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

class NeverFail implements FailureStrategyInterface
{
    public function hasFailed()
    {
        return false;
    }
}

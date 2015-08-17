<?php

namespace Honeybee\Infrastructure\Job;

interface JobInterface
{
    const STATE_FRESH = 1;

    const STATE_SUCCESS = 2;

    const STATE_ERROR = 3;

    const STATE_FATAL = 4;

    public function run(array $parameters = array());
}

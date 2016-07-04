<?php

namespace Honeybee\Infrastructure\Job;

interface JobServiceInterface
{
    public function dispatch(JobInterface $job, $exchange_name);
}

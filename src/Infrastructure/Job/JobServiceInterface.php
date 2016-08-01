<?php

namespace Honeybee\Infrastructure\Job;

use Closure;

interface JobServiceInterface
{
    public function dispatch(JobInterface $job, $exchange_name);

    public function retry(JobInterface $job, $exchange_name, array $metadata = []);

    public function fail(JobInterface $job, array $metadata = []);

    public function consume($queue_name, Closure $message_callback);
}

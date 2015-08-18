<?php

namespace Honeybee\Infrastructure\Job;

use Honeybee\Infrastructure\Config\SettingsInterface;

interface JobServiceInterface
{
    public function dispatch(JobInterface $job, SettingsInterface $settings = null);
}
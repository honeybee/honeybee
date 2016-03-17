<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class MultiplyInterval implements RetryStrategyInterface
{
    const DEFAULT_INTERVAL = 1; //seconds

    const DEFAULT_MULTIPLIER = 2;

    const DEFAULT_MAX_INTERVAL = 86400;

    protected $job;

    protected $interval;

    protected $multiplier;

    protected $max_interval;

    public function __construct(JobInterface $job, SettingsInterface $settings)
    {
        //@todo support PHP time strings
        $this->job = $job;
        $this->interval = (int)$settings->get('interval', self::DEFAULT_INTERVAL);
        $this->multiplier = (int)$settings->get('multiplier', self::DEFAULT_MULTIPLIER);
        $this->max_interval = (int)$settings->get('max_interval', self::DEFAULT_MAX_INTERVAL);
    }

    public function getInterval()
    {
        $meta_data = $this->job->getMetaData();
        $retries = isset($meta_data['retries']) ? $meta_data['retries'] : 0;

        $interval = $this->interval;
        if ($retries > 0) {
            $interval = pow($this->multiplier, $retries) * $this->interval;
            if ($this->max_interval) {
                $interval = min($interval, $this->max_interval);
            }
        }

        return $interval * 1000; //milliseconds
    }
}

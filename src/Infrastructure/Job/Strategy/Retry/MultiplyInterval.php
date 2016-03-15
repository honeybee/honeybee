<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class MultiplyInterval implements RetryStrategyInterface
{
    const DEFAULT_INTERVAL = 1; //seconds

    const DEFAULT_MULTIPLIER = 2;

    const DEFAULT_MAX_INTERVAL = 86400;

    protected $interval;

    protected $multiplier;

    protected $max_interval;

    public function __construct(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        //@todo support PHP time strings
        $this->interval = (int)$settings->get('interval', self::DEFAULT_INTERVAL);
        $this->multiplier = (int)$settings->get('multiplier', self::DEFAULT_MULTIPLIER);
        $this->max_interval = (int)$settings->get('max_interval', self::DEFAULT_MAX_INTERVAL);
    }

    public function getInterval(JobInterface $job)
    {
        $meta_data = $job->getMetaData();
        $retries = isset($meta_data['retries']) ? $meta_data['retries'] : 0;

        $interval = $this->interval;
        if ($retries > 0) {
            $interval = pow($this->multiplier, $retries) * $this->interval;
            if ($this->max_interval) {
                $interval = min($interval, $this->max_interval);
            }
        }

        return $interval;
    }
}

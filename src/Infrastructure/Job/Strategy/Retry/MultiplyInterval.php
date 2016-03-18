<?php

namespace Honeybee\Infrastructure\Job\Strategy\Retry;

use DateInterval;
use DateTimeImmutable;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class MultiplyInterval implements RetryStrategyInterface
{
    const DEFAULT_INTERVAL = '1 second';

    const DEFAULT_MULTIPLIER = 2;

    const DEFAULT_MAX_INTERVAL = '1 day';

    protected $job;

    protected $interval;

    protected $multiplier;

    protected $max_interval;

    public function __construct(JobInterface $job, SettingsInterface $settings)
    {
        $this->job = $job;
        $this->interval = $settings->get('interval', self::DEFAULT_INTERVAL);
        $this->multiplier = $settings->get('multiplier', self::DEFAULT_MULTIPLIER);
        $this->max_interval = $settings->get('max_interval', self::DEFAULT_MAX_INTERVAL);
    }

    public function getInterval()
    {
        $meta_data = $this->job->getMetaData();
        $retries = isset($meta_data['retries']) ? $meta_data['retries'] : 0;

        // assume settings are seconds if provided as integers
        $zero_date = new DateTimeImmutable('@0');
        $interval = is_numeric($this->interval)
            ? $this->interval
            : $zero_date->add(DateInterval::createFromDateString($this->interval))->getTimestamp();

        if ($retries > 0) {
            $interval = pow($this->multiplier, $retries) * $interval;
            if ($this->max_interval) {
                $interval = min(
                    $interval,
                    is_numeric($this->max_interval)
                    ? $this->max_interval
                    : $zero_date->add(DateInterval::createFromDateString($this->max_interval))->getTimestamp()
                );
            }
        }

        return (int)($interval * 1000); //milliseconds
    }
}

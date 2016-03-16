<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use DateInterval;
use DateTimeImmutable;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobInterface;

class ExpiresAfter implements FailureStrategyInterface
{
    protected $interval;

    public function __construct(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        if (!$settings->has('interval')) {
            throw new RuntimeError('ExpiresAfter strategy requires "interval" setting.');
        }

        $this->interval = $settings->get('interval');
    }

    public function hasFailed(JobInterface $job)
    {
        $now_date = new DateTimeImmutable;
        $event_date = new DateTimeImmutable($job->getEvent()->getIsoDate());
        $expiry_date = $event_date->add(DateInterval::createFromDateString($this->interval));
        return $now_date > $expiry_date;
    }
}

<?php

namespace Honeybee\Infrastructure\Job\Strategy\Failure;

use DateInterval;
use DateTimeImmutable;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;

class ExpiresAfter implements FailureStrategyInterface
{
    protected $job;

    protected $interval;

    public function __construct(JobInterface $job, SettingsInterface $settings)
    {
        if (!$settings->has('interval')) {
            throw new RuntimeError('ExpiresAfter strategy requires "interval" setting.');
        }

        $this->job = $job;
        $this->interval = $settings->get('interval');

        if (!(new DateTimeImmutable('@0'))->add(DateInterval::createFromDateString($this->interval))->getTimestamp()) {
            throw new RuntimeError('ExpiresAfter strategy "interval" setting should be a valid time string.');
        }
    }

    public function hasFailed()
    {
        $now_date = new DateTimeImmutable;
        $event_date = new DateTimeImmutable($this->job->getIsoDate());
        $expiry_date = $event_date->add(DateInterval::createFromDateString($this->interval));
        return $now_date > $expiry_date;
    }
}

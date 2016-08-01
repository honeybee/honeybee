<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Assert\Assertion;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    protected $job_service;

    protected $exchange;

    public function __construct($name, JobServiceInterface $job_service, $exchange)
    {
        Assertion::string($exchange);

        parent::__construct($name);
        $this->job_service = $job_service;
        $this->exchange = $exchange;
    }

    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;
        $job_name = $settings->get('job');

        Assertion::string($channel_name);
        Assertion::notEmpty($channel_name);
        Assertion::integer($subscription_index);
        Assertion::string($job_name);
        Assertion::notEmpty($job_name);

        $job = $this->job_service->createJob(
            [
                'event' => $event,
                'channel' => $channel_name,
                'subscription_index' => $subscription_index
            ],
            $job_name
        );

        $this->job_service->dispatch($job, $this->exchange);
    }
}

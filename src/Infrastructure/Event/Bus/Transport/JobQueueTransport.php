<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    const DEFAULT_EVENT_EXCHANGE = 'honeybee.domain.events';

    protected $job_service;

    protected $exchange_name;

    public function __construct($name, JobServiceInterface $job_service, SettingsInterface $settings = null)
    {
        parent::__construct($name);

        $settings = $settings ?: new Settings;

        $this->exchange_name = $settings->get('exchange', self::DEFAULT_EVENT_EXCHANGE);

        $this->job_service = $job_service;
        $this->job_service->initialize($this->exchange_name);
    }

    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        $job = $this->job_service->createJob(
            [
                'event' => $event,
                'channel' => $channel_name,
                'subscription_index' => $subscription_index
            ],
            $settings->get('job')
        );

        $this->job_service->dispatch($job, $this->exchange_name);
    }
}

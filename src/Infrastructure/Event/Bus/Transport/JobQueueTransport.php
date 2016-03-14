<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    const DEFAULT_EVENT_EXCHANGE = 'honeybee.domain.events';

    const DEFAULT_WAIT_EXCHANGE = 'honeybee.domain.waiting';

    const DEFAULT_WAIT_QUEUE = 'honeybee.events.waiting';

    const DEFAULT_FAILURE_CHANNEL = 'honeybee.events.failed';

    const DEFAULT_ROUTING_KEY = 'default';

    protected $job_service;

    protected $exchange;

    protected $wait_exchange;

    protected $wait_queue;

    protected $routing_key;

    public function __construct($name, JobServiceInterface $job_service, SettingsInterface $settings = null)
    {
        parent::__construct($name);

        $settings = $settings ?: new Settings;

        $this->job_service = $job_service;
        $this->exchange = $settings->get('exchange', self::DEFAULT_EVENT_EXCHANGE);
        $this->wait_exchange = $settings->get('wait_exchange', self::DEFAULT_WAIT_EXCHANGE);
        $this->wait_queue = $settings->get('wait_queue', self::DEFAULT_WAIT_QUEUE);
        $this->routing_key = $settings->get('routing_key', self::DEFAULT_ROUTING_KEY);

        $this->job_service->initialize(
            new Settings([
                'exchange' => $this->exchange,
                'wait_exchange' => $this->wait_exchange,
                'wait_queue' => $this->wait_queue,
                'routing_key' => $this->routing_key
            ])
        );
    }

    public function send($channel_name, EventInterface $event, $subscription_index, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        $job = $this->job_service->createJob(
            $settings->get('job'),
            [
                'event' => $event,
                'channel' => $channel_name,
                'subscription_index' => $subscription_index
            ]
        );

        $this->job_service->dispatch(
            $job,
            new Settings([
                'exchange' => $this->exchange,
                'queue' => $job->getSettings()->get('queue'),
                'routing_key' => $this->routing_key
            ])
        );
    }
}

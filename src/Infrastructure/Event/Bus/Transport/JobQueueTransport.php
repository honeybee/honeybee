<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Job\Bundle\ExecuteEventHandlersJob;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    protected $exchange;

    protected $queue_name;

    protected $job_service;

    public function __construct($name, $exchange, JobServiceInterface $job_service, $queue_name = null)
    {
        parent::__construct($name);

        $this->exchange = $exchange;
        $this->queue_name = $queue_name;
        $this->job_service = $job_service;
    }

    public function send($channel_name, EventInterface $event, $subscription_index)
    {
        $job_state = [
            ExecuteEventHandlersJob::OBJECT_TYPE => ExecuteEventHandlersJob::CLASS,
            'event' => $event,
            'channel' => $channel_name,
            'subscription_index' => $subscription_index
        ];

        $this->job_service->dispatch(
            $this->job_service->createJob($job_state),
            new Settings(
                [
                    'route_key' => $this->queue_name,
                    'exchange' => $this->exchange
                ]
            )
        );
    }
}

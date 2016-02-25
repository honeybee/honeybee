<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Job\Bundle\ExecuteEventHandlersJob;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    const DEFAULT_MSG_ROUTE = 'default';

    protected $exchange;

    protected $msg_route;

    protected $job_service;

    public function __construct($name, $exchange, JobServiceInterface $job_service, $msg_route = null)
    {
        parent::__construct($name);

        $this->exchange = $exchange;
        $this->msg_route = $msg_route ?: self::DEFAULT_MSG_ROUTE;
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
                    'routing_key' => $this->msg_route,
                    'exchange' => $this->exchange
                ]
            )
        );
    }
}

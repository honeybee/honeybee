<?php

namespace Honeybee\Infrastructure\Event\Bus\Transport;

use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Job\Bundle\ExecuteEventHandlersJob;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends EventTransport
{
    protected $exchange;

    protected $job_service;

    public function __construct($name, $exchange, JobServiceInterface $job_service)
    {
        parent::__construct($name);

        $this->exchange = $exchange;
        $this->job_service = $job_service;
    }

    public function send($channel_name, EventInterface $event)
    {
        $job_state = [
            ExecuteEventHandlersJob::OBJECT_TYPE => ExecuteEventHandlersJob::CLASS,
            'event' => $event,
            'channel' => $channel_name
        ];

        $this->job_service->dispatch(
            $this->job_service->createJob($job_state),
            new Settings(
                array(
                    'route_key' => $event->getType(),
                    'exchange' => $this->exchange
                )
            )
        );
    }
}

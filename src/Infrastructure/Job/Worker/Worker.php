<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Infrastructure\Job\JobService;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;

class Worker implements WorkerInterface
{
    protected $running = false;

    protected $job_service;

    protected $config;

    public function __construct(JobServiceInterface $job_service, ConfigInterface $config)
    {
        //@note probably better if we could specify a channel and load the transport instead of
        //specifying service configuration on the command line
        $this->config = $config;
        $this->job_service = $job_service;
    }

    public function run()
    {
        if ($this->running === true) {
            return false;
        }
        $this->running = true;

        $this->validateSetup();
        $channel = $this->connectChannel();
        while ($this->running && count($channel->callbacks)) {
            $channel->wait();
        }
        $this->running = false;
    }

    protected function validateSetup()
    {
        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');
        $job = $this->config->get('job');

        if (!$exchange_name) {
            throw new RuntimeError("Missing required 'exchange' config setting.");
        }
        if (!$queue_name) {
            throw new RuntimeError("Missing required 'queue' config setting.");
        }
        if (!$job) {
            throw new RuntimeError("Missing required 'job' config setting.");
        }
    }

    protected function connectChannel()
    {
        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');
        $routing_key = $this->config->get('bindings')[0];

        $this->job_service->initialize($exchange_name);
        $this->job_service->initializeQueue($exchange_name, $queue_name, $routing_key);

        $message_callback = function ($message) {
            $this->onJobScheduledForExecution($message);
        };

        return $this->job_service->consume($queue_name, $message_callback);
    }

    protected function onJobScheduledForExecution($job_message)
    {
        try {
            $delivery_info = $job_message->delivery_info;
            $channel = $delivery_info['channel'];
            $delivery_tag = $delivery_info['delivery_tag'];
            $job_state = JsonToolkit::parse($job_message->body);
            $job = $this->job_service->createJob($job_state, $this->config->get('job'));
            $job->run();
        } catch (Exception $error) {
            if ($job->canRetry()) {
                $this->job_service->retry($job, $delivery_info['exchange'] . JobService::WAIT_SUFFIX);
            } else {
                $this->job_service->fail($job, $delivery_info['exchange'], $error);
            }
        }

        // acknowledge the message to remove it from the event queue.
        // an 'ack' is effectively the same as a 'nack'.
        $channel->basic_ack($delivery_tag);
    }
}

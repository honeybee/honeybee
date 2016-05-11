<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Infrastructure\Job\JobService;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;

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
        $job = $this->config->get('job');

        if (!$exchange_name) {
            throw new RuntimeError("Missing required 'exchange' config setting.");
        }

        if (!$job) {
            throw new RuntimeError("Missing required 'job' config setting.");
        }
    }

    protected function connectChannel()
    {
        $exchange_name = $this->config->get('exchange');
        $job_name = $this->config->get('job');
        $job_settings = $this->job_service->getJob($job_name)->get('settings');
        $queue_name = $job_settings->get('queue');
        $routing_key = $job_settings->get('routing_key');

        $this->job_service->initialize($exchange_name);
        $this->job_service->initializeQueue($exchange_name, $queue_name, $routing_key);

        $message_callback = function ($message) {
            $this->onJobScheduledForExecution($message);
        };

        return $this->job_service->consume($queue_name, $message_callback);
    }

    protected function onJobScheduledForExecution($job_message)
    {
        $delivery_info = $job_message->delivery_info;
        $channel = $delivery_info['channel'];
        $delivery_tag = $delivery_info['delivery_tag'];
        $job_state = JsonToolkit::parse($job_message->body);
        $job = $this->job_service->createJob($job_state, $this->config->get('job'));

        try {
            $job->run();
        } catch (Exception $error) {
            if ($job->getStrategy()->canRetry()) {
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

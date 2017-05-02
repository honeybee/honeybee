<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Assert\Assertion;
use Exception;
use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Psr\Log\LoggerInterface;

class Worker implements WorkerInterface
{
    protected $running = false;

    protected $job_service;

    protected $config;

    protected $logger;

    public function __construct(LoggerInterface $logger, JobServiceInterface $job_service, ConfigInterface $config)
    {
        //@note probably better if we could specify a channel and load the transport instead of
        //specifying service configuration on the command line
        $this->config = $config;
        $this->job_service = $job_service;
        $this->logger = $logger;
    }

    public function run()
    {
        if ($this->running === true) {
            return false;
        }
        $this->running = true;

        $channel = $this->connectChannel();
        while ($this->running && count($channel->callbacks)) {
            $channel->wait();
        }
        $this->running = false;
    }

    protected function connectChannel()
    {
        $queue = $this->config->get('queue');

        Assertion::string($queue);
        Assertion::notEmpty($queue);

        $message_callback = function ($message) {
            $this->onJobScheduledForExecution($message);
        };

        return $this->job_service->consume($queue, $message_callback);
    }

    protected function onJobScheduledForExecution($job_message)
    {
        $delivery_info = $job_message->delivery_info;
        $channel = $delivery_info['channel'];
        $delivery_tag = $delivery_info['delivery_tag'];
        $job_state = JsonToolkit::parse($job_message->body);

        $job = $this->job_service->createJob($job_state);

        try {
            $job->run();
        } catch (Exception $error) {
            $this->logger->error($error->getMessage().PHP_EOL.$error->getTraceAsString());
            if ($job->getStrategy()->canRetry()) {
                $this->job_service->retry($job, $delivery_info['exchange'] . '.waiting');
            } else {
                $this->job_service->fail(
                    $job,
                    [
                        'error_message' => $error->getMessage(),
                        'error_trace' => $error->getTraceAsString()
                    ]
                );
            }
        }

        // acknowledge the message to remove it from the event queue.
        // an 'ack' is effectively the same as a 'nack'.
        $channel->basic_ack($delivery_tag);
    }
}

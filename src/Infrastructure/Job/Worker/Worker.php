<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Common\Error\ParseError;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class Worker implements WorkerInterface
{
    protected $running = false;

    protected $connector;

    protected $config;

    protected $job_service;

    public function __construct(RabbitMqConnector $connector, ConfigInterface $config, JobServiceInterface $job_service)
    {
        $this->connector = $connector;
        $this->config = $config;
        $this->job_service = $job_service;
    }

    public function run()
    {
        if ($this->running === true) {
            return false;
        }

        $this->validateSetup();

        $this->running = true;

        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');
        $channel = $this->connector->getConnection()->channel();

        $channel->basic_qos(null, 1, null);
        $channel->exchange_declare($exchange_name, 'direct', false, true, false);
        $channel->queue_declare($queue_name, false, true, false, false);

        $bindings = (array)$this->config->get('bindings', []);
        if (empty($bindings)) {
            $channel->queue_bind($queue_name, $exchange_name);
        } else {
            foreach ($bindings as $binding) {
                $channel->queue_bind($queue_name, $exchange_name, $binding);
            }
        }

        $message_callback = function ($message) {
            $this->onMessageReceived($message);
        };
        $channel->basic_consume($queue_name, false, true, false, false, false, $message_callback);

        while ($this->running && count($channel->callbacks)) {
            $channel->wait();
        }
        $this->running = false;
    }

    protected function validateSetup()
    {
        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');

        if (!$exchange_name) {
            throw new RuntimeError("Missing required 'exchange_name' config setting.");
        }
        if (!$queue_name) {
            throw new RuntimeError("Missing required 'queue_name' config setting.");
        }
    }

    protected function onMessageReceived($job_message)
    {
        $delivery_info = $job_message->delivery_info;
        $channel = $delivery_info['channel'];
        $delivery_tag = $delivery_info['delivery_tag'];

        try {
            $execution_state = $this->job_service->createJob(JsonToolkit::parse($job_message->body))->run();
        } catch (Exception $runtime_error) {
            $execution_state = JobInterface::STATE_FATAL;
            // @todo appropiate error-logging
            error_log(__METHOD__ . ' - ' . $runtime_error->getMessage());
        }

        switch ($execution_state) {
            case JobInterface::STATE_SUCCESS:
                $channel->basic_ack($delivery_tag);
                break;

            case JobInterface::STATE_ERROR:
                // @todo log error
                $channel->basic_nack($delivery_tag);
                break;

            case JobInterface::STATE_FATAL:
                // @todo the job is now dropped from queue as fatal.
                // we might want to push it to an error queue or to a journal/recovery file for fatal jobs.
                $channel->basic_reject($delivery_tag);
                break;
        }
    }
}

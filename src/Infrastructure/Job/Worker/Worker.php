<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;

class Worker implements WorkerInterface
{
    const DEFAULT_RETRY_LIMIT = 3;

    protected $running = false;

    protected $connector;

    protected $config;

    protected $job_service;

    protected $retry_tracking;

    public function __construct(RabbitMqConnector $connector, ConfigInterface $config, JobServiceInterface $job_service)
    {
        $this->connector = $connector;
        $this->config = $config;
        $this->job_service = $job_service;
        $retry_tracking = [];
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

        if (!$exchange_name) {
            throw new RuntimeError("Missing required 'exchange_name' config setting.");
        }
        if (!$queue_name) {
            throw new RuntimeError("Missing required 'queue_name' config setting.");
        }
    }

    protected function connectChannel()
    {
        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');
        $channel = $this->connector->getConnection()->channel();

        $channel->basic_qos(null, 1, null);
        $channel->exchange_declare($exchange_name, 'direct', false, true, false);
        $channel->queue_declare($queue_name, false, true, false, false);

        $bindings = (array)$this->config->get('bindings', []);
        if (empty($bindings)) {
            $channel->queue_bind($queue_name, $exchange_name, 'default');
        } else {
            foreach ($bindings as $binding) {
                $channel->queue_bind($queue_name, $exchange_name, $binding);
            }
        }

        $message_callback = function ($message) {
            $this->onJobScheduledForExecution($message);
        };
        $channel->basic_consume($queue_name, false, true, false, false, false, $message_callback);

        return $channel;
    }

    protected function onJobScheduledForExecution($job_message)
    {
        $job_failed = true;

        try {
            $job = $this->job_service->createJob(JsonToolkit::parse($job_message->body));
            $job->run();
            $job_failed = false;
        } catch (Exception $runtime_error) {
            throw $runtime_error;
            $jid = $job->getUuid();
            $job_success = false;
            if (!isset($this->retry_tracking[$jid])) {
                $this->retry_tracking[$jid] = 0;
            }
            $this->retry_tracking[$jid]++;
            // @todo appropiate error-logging
            error_log(__METHOD__ . ' - ' . $runtime_error->getMessage() . PHP_EOL . $runtime_error->getMessage());
printf(
    "\n\n[Worker] Unexpected error during execution of job(id) '%s' with message %s and trace:\n%s\n\n",
    $jid,
    $runtime_error->getMessage(),
    $runtime_error->getTraceAsString()
);
        }

        $delivery_info = $job_message->delivery_info;
        $channel = $delivery_info['channel'];
        $delivery_tag = $delivery_info['delivery_tag'];
        if ($job_failed) {
            if ($this->retry_tracking[$jid] <= $this->getMaxRetriesAllowedFor($job)) {
                $channel->basic_reject($delivery_tag, true);
            } else {
                // @todo the job is now dropped from queue as fatal.
                // we might want to push it to an error queue or to a journal/recovery file for fatal jobs.
                unset($this->retry_tracking[$jid]);
                $channel->basic_reject($delivery_tag, false);
            }
        } else {
            $channel->basic_ack($delivery_tag);
        }
    }

    protected function getMaxRetriesAllowedFor(JobInterface $job)
    {
        // @todo make this configurable based on metrics like job-type, -frequence etc.
        return self::DEFAULT_RETRY_LIMIT;
    }
}

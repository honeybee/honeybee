<?php

namespace Honeybee\Infrastructure\Job\Worker;

use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\JsonToolkit;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Job\JobInterface;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\Infrastructure\Job\Bundle\ExecuteEventHandlersJob;
use Honeybee\Infrastructure\Event\FailedJobEvent;
use Honeybee\Infrastructure\Config\Settings;

class Worker implements WorkerInterface
{
    const DEFAULT_MIN_EXPIRATION = 4000;

    const DEFAULT_EXPIRATION_MULTIPLIER = 3;

    const DEFAULT_MAX_EXPIRATION = 86400000;

    const DEFAULT_FAILURE_CHANNEL = 'honeybee.events.failed';

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
        $wait_exchange_name = $this->config->get('wait_exchange');
        $wait_queue_name = $this->config->get('wait_queue');

        if (!$exchange_name) {
            throw new RuntimeError("Missing required 'exchange' config setting.");
        }
        if (!$queue_name) {
            throw new RuntimeError("Missing required 'queue' config setting.");
        }
        if (!$wait_exchange_name) {
            throw new RuntimeError("Missing required 'wait_exchange' config setting.");
        }
        if (!$wait_queue_name) {
            throw new RuntimeError("Missing required 'wait_queue' config setting.");
        }
    }

    protected function connectChannel()
    {
        $exchange_name = $this->config->get('exchange');
        $queue_name = $this->config->get('queue');
        $wait_exchange_name = $this->config->get('wait_exchange');
        $wait_queue_name = $this->config->get('wait_queue');
        $channel = $this->connector->getConnection()->channel();

        $channel->basic_qos(null, 1, null);
        $channel->exchange_declare($exchange_name, 'direct', false, true, false);
        $channel->queue_declare($queue_name, false, true, false, false);
        $channel->exchange_declare($wait_exchange_name, 'direct', false, true, false);
        $channel->queue_declare($wait_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $exchange_name ]
        ]);

        // bind routing keys to both exchange and wait exchange to enable DLX retry
        $bindings = (array)$this->config->get('bindings', []);
        if (empty($bindings)) {
            $channel->queue_bind($queue_name, $exchange_name, 'default');
            $channel->queue_bind($wait_queue_name, $wait_exchange_name, 'default');
        } else {
            foreach ($bindings as $binding) {
                $channel->queue_bind($queue_name, $exchange_name, $binding);
                $channel->queue_bind($wait_queue_name, $wait_exchange_name, $binding);
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
        try {
            $delivery_info = $job_message->delivery_info;
            $channel = $delivery_info['channel'];
            $delivery_tag = $delivery_info['delivery_tag'];
            $job_state = JsonToolkit::parse($job_message->body);
            $job = $this->job_service->createJob($job_state);
            $job->run();
        } catch (Exception $runtime_error) {
            // @todo appropiate error-logging
            error_log(__METHOD__ . ' - ' . $runtime_error->getMessage() . PHP_EOL . $runtime_error->getMessage());
            printf(
                "\n\n[Worker] Unexpected error during execution of job(id) '%s' with message: %s and trace:\n%s\n\n",
                $job->getUuid(),
                $runtime_error->getMessage(),
                $runtime_error->getTraceAsString()
            );

            $expiration = $this->getExpirationIntervalFor($job_message);
            if ($expiration !== false) {
                // republish failed message to wait exchange with new expiration time
                $job_message->set('expiration', $expiration);
                $this->job_service->publish($job_message, new Settings(
                    [
                        'exchange' => $this->config->get('wait_exchange'),
                        'routing_key' => $delivery_info['routing_key']
                    ]
                ));
            } else {
                // handle failed message by publishing a failure event to the same queue
                $this->job_service->dispatch(
                    $this->job_service->createJob([
                        ExecuteEventHandlersJob::OBJECT_TYPE => ExecuteEventHandlersJob::CLASS,
                        'event' => new FailedJobEvent(
                            [
                                'job_state' => $job_state,
                                'delivery_info' => $delivery_info,
                                'message_headers' => $this->getMessageHeaders($job_message)
                            ]
                        ),
                        'channel' => self::DEFAULT_FAILURE_CHANNEL,
                        'subscription_index' => 0
                    ]),
                    new Settings($delivery_info)
                );
            }
        }

        // acknowledge the message to remove it from the event queue. An 'ack' is effectively the
        // same as a 'nack'.
        $channel->basic_ack($delivery_tag);
    }

    protected function getMessageHeaders($job_message)
    {
        return $job_message->has('application_headers')
            ? $job_message->get('application_headers')->getNativeData()
            : [];
    }

    protected function getExpirationIntervalFor($job_message)
    {
        $expiration = self::DEFAULT_MIN_EXPIRATION;
        $headers = $this->getMessageHeaders($job_message);
        //@note the size of the x-death array is the number of attempts for the message
        if (isset($headers['x-death'][0]['original-expiration'])) {
            $original_expiration = $headers['x-death'][0]['original-expiration'];
            // fail message when previous attempt reaches maximum interval
            if ($original_expiration >= self::DEFAULT_MAX_EXPIRATION) {
                $expiration = false;
            } else {
                $expiration = min(
                    $original_expiration * self::DEFAULT_EXPIRATION_MULTIPLIER,
                    self::DEFAULT_MAX_EXPIRATION
                );
            }
        }
        return $expiration;
    }
}

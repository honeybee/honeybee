<?php

namespace Honeybee\Infrastructure\Job;

use Closure;
use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Job\Bundle\ExecuteEventHandlersJob;
use Honeybee\Infrastructure\Event\FailedJobEvent;
use Honeybee\Infrastructure\Event\Bus\Transport\JobQueueTransport;
use Honeybee\ServiceLocatorInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class JobService implements JobServiceInterface
{
    protected $connector;

    protected $service_locator;

    protected $config;

    protected $logger;

    protected $channel;

    public function __construct(
        RabbitMqConnector $connector,
        ServiceLocatorInterface $service_locator,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->service_locator = $service_locator;
        $this->connector = $connector;
        $this->logger = $logger;
    }

    public function initialize(SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        $exchange_name = $settings->get('exchange', $this->config->get('exchange'));
        if (!$exchange_name) {
            throw new RuntimeError('Missing required "exchange" setting for JobService initialize call.');
        }

        $wait_exchange_name = $settings->get('wait_exchange', $this->config->get('wait_exchange'));
        if (!$wait_exchange_name) {
            throw new RuntimeError('Missing required "wait_exchange" setting for JobService initialize call.');
        }

        $wait_queue_name = $settings->get('wait_queue', $this->config->get('wait_queue'));
        if (!$wait_queue_name) {
            throw new RuntimeError('Missing required "wait_queue" setting for JobService initialize call.');
        }

        $routing_key = $settings->get('routing_key', $this->config->get('routing_key'));
        if (!$routing_key) {
            throw new RuntimeError('Missing required "routing_key" setting for JobService initialize call.');
        }

        $this->channel = $this->connector->getConnection()->channel();
        $this->channel->exchange_declare($exchange_name, 'direct', false, true, false);
        $this->channel->exchange_declare($wait_exchange_name, 'direct', false, true, false);
        $this->channel->queue_declare($wait_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $exchange_name ]
        ]);
        $this->channel->queue_bind($wait_queue_name, $wait_exchange_name, $routing_key);

        if ($settings->has('queue')) {
            $this->initializeQueue($settings);
        }
    }

    protected function initializeQueue(SettingsInterface $settings)
    {
        $queue_name = $settings->get('queue');
        if (!$queue_name) {
            throw new RuntimeError('Missing required "queue" setting for JobService initializeQueue call.');
        }

        $exchange_name = $settings->get('exchange');
        if (!$exchange_name) {
            throw new RuntimeError('Missing required "exchange" setting for JobService initializeQueue call.');
        }

        $routing_key = $settings->get('routing_key');
        if (!$routing_key) {
            throw new RuntimeError('Missing required "routing_key" setting for JobService initializeQueue call.');
        }

        $this->channel->queue_declare($queue_name, false, true, false, false);
        $this->channel->queue_bind($queue_name, $exchange_name, $routing_key);
    }

    public function dispatch(JobInterface $job, SettingsInterface $settings = null)
    {
        $this->initializeQueue($settings);

        $message_payload = json_encode($job->toArray());
        $message = new AMQPMessage($message_payload, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        $exchange_name = $settings->get('exchange', $this->config->get('exchange'));
        $this->channel->basic_publish($message, $exchange_name, $settings->get('routing_key'));
    }

    public function consume($queue_name, Closure $message_callback)
    {
        if (!$this->channel) {
            throw new RuntimeError('Channel has not been initialized prior to consume.');
        }

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue_name, false, true, false, false, false, $message_callback);

        return $this->channel;
    }

    public function retryJob(array $job_state, SettingsInterface $settings)
    {
        if (!$settings->has('retry_interval')) {
            throw new RuntimeError('Retry interval has not been specified for job retry.');
        }

        $job_state['meta_data']['retries'] = isset($job_state['meta_data']['retries'])
            ? ++$job_state['meta_data']['retries'] : 1;
        $message_payload = json_encode($job_state);
        $message = new AMQPMessage(
            $message_payload,
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'expiration' => $settings->get('retry_interval')
            ]
        );

        $wait_exchange_name = $settings->get('wait_exchange', $this->config->get('wait_exchange'));
        $this->channel->basic_publish($message, $wait_exchange_name, $settings->get('routing_key'));
    }

    public function failJob(array $job_state, Exception $error, SettingsInterface $settings)
    {
        $failed_job = $this->createJob(
            [
                ExecuteEventHandlersJob::OBJECT_TYPE => ExecuteEventHandlersJob::CLASS,
                'event' => new FailedJobEvent([
                    'failed_job_state' => $job_state,
                    'meta_data' => [
                        'error_message' => $error->getMessage(),
                        'error_trace' => $error->getTraceAsString()
                    ]
                ]),
                'channel' => JobQueueTransport::DEFAULT_FAILURE_CHANNEL,
                'subscription_index' => 0
            ]
        );
        $message_payload = json_encode($failed_job->toArray());
        $message = new AMQPMessage($message_payload, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        $exchange_name = $settings->get('exchange', $this->config->get('exchange'));
        $this->channel->basic_publish($message, $exchange_name, $settings->get('routing_key'));
    }

    public function createJob(array $job_state)
    {
        $job_class = $job_state['@type'];

        if (!class_exists($job_class)) {
            throw new RuntimeError("Unable to resolve job implementor: " . $job_class);
        }

        return $this->service_locator->createEntity($job_class, [ ':state' => $job_state ]);
    }
}

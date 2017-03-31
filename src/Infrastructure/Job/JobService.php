<?php

namespace Honeybee\Infrastructure\Job;

use Assert\Assertion;
use Closure;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Infrastructure\Event\FailedJobEvent;
use Honeybee\ServiceLocatorInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class JobService implements JobServiceInterface
{
    protected $connector;

    protected $service_locator;

    protected $event_bus;

    protected $job_map;

    protected $config;

    protected $logger;

    protected $channel;

    public function __construct(
        RabbitMqConnector $connector,
        ServiceLocatorInterface $service_locator,
        EventBusInterface $event_bus,
        JobMap $job_map,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->service_locator = $service_locator;
        $this->event_bus = $event_bus;
        $this->job_map = $job_map;
        $this->connector = $connector;
        $this->logger = $logger;
    }

    public function dispatch(JobInterface $job, $exchange_name)
    {
        $routing_key = $job->getSettings()->get('routing_key', '');

        Assertion::string($exchange_name);
        Assertion::string($routing_key);

        $message_payload = json_encode($job->toArray());
        $message = new AMQPMessage($message_payload, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        $this->getChannel()->basic_publish($message, $exchange_name, $routing_key);
    }

    public function consume($queue_name, Closure $message_callback)
    {
        Assertion::string($queue_name);
        Assertion::notEmpty($queue_name);

        $channel = $this->getChannel();

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, false, true, false, false, false, $message_callback);

        return $channel;
    }

    public function retry(JobInterface $job, $exchange_name, array $metadata = [])
    {
        $routing_key = $job->getSettings()->get('routing_key', '');

        Assertion::string($exchange_name);
        Assertion::string($routing_key);

        $job_state = $job->toArray();
        $job_state['metadata']['retries'] = isset($job_state['metadata']['retries'])
            ? ++$job_state['metadata']['retries'] : 1;

        /*
         * @todo better to retry by distributing the event back on the event bus and
         * calculating the expiration at that stage
         */
        $message = new AMQPMessage(
            json_encode($job_state),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'expiration' => $job->getStrategy()->getRetryInterval()
            ]
        );

        $this->getChannel()->basic_publish($message, $exchange_name, $routing_key);
    }

    public function fail(JobInterface $job, array $metadata = [])
    {
        $event = new FailedJobEvent([
            'failed_job_state' => $job->toArray(),
            'metadata' => $metadata
        ]);

        $this->event_bus->distribute(ChannelMap::CHANNEL_FAILED, $event);
    }

    /**
     * @todo Job building and JobMap should be provisioned and injected where required, and
     * so the following public methods are not specified on the interface.
     */
    public function createJob(array $job_state)
    {
        if (!isset($job_state['metadata']['job_name']) || empty($job_state['metadata']['job_name'])) {
            throw new RuntimeError('Unable to get job name from metadata.');
        }

        $job_name = $job_state['metadata']['job_name'];

        $job_config = $this->getJob($job_name);
        $strategy_config = $job_config['strategy'];
        $service_locator = $this->service_locator;

        $strategy_callback = function (JobInterface $job) use ($service_locator, $strategy_config) {
            $strategy_implementor = $strategy_config['implementor'];

            $retry_strategy = $service_locator->make(
                $strategy_config['retry']['implementor'],
                [ ':job' => $job, ':settings' => $strategy_config['retry']['settings'] ]
            );

            $failure_strategy = $service_locator->make(
                $strategy_config['failure']['implementor'],
                [ ':job' => $job, ':settings' => $strategy_config['failure']['settings'] ]
            );

            return new $strategy_implementor($retry_strategy, $failure_strategy);
        };

        return $this->service_locator->make(
            $job_config['class'],
            [
                // job class cannot be overridden by state
                ':state' => [ '@type' => $job_config['class'] ] + $job_state,
                ':strategy_callback' => $strategy_callback,
                ':settings' => $job_config['settings']
            ]
        );
    }

    public function getJobMap()
    {
        return $this->job_map;
    }

    public function getJob($job_name)
    {
        $job_config = $this->job_map->get($job_name);

        if (!$job_config) {
            throw new RuntimeError(sprintf('Configuration for job "%s" was not found.', $job_name));
        }

        return $job_config;
    }

    protected function getChannel()
    {
        if (!$this->channel) {
            $this->channel = $this->connector->getConnection()->channel();
        }

        return $this->channel;
    }
}

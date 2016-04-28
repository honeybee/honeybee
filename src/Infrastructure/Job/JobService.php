<?php

namespace Honeybee\Infrastructure\Job;

use Closure;
use Exception;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use Honeybee\Infrastructure\Event\FailedJobEvent;
use Honeybee\Infrastructure\Event\Bus\Channel\ChannelMap;
use Honeybee\Infrastructure\Event\Bus\Transport\JobQueueTransport;
use Honeybee\ServiceLocatorInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class JobService implements JobServiceInterface
{
    const DEFAULT_JOB = 'honeybee.jobs.execute_handlers';

    const WAIT_SUFFIX = '.waiting';

    const UNROUTED_SUFFIX = '.unrouted';

    const REPUB_SUFFIX = '.repub';

    const QUEUE_SUFFIX = '.q';

    const REPUB_INTERVAL = 30000; //30 seconds

    protected $connector;

    protected $service_locator;

    protected $job_map;

    protected $config;

    protected $logger;

    protected $channel;

    public function __construct(
        RabbitMqConnector $connector,
        ServiceLocatorInterface $service_locator,
        JobMap $job_map,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->service_locator = $service_locator;
        $this->job_map = $job_map;
        $this->connector = $connector;
        $this->logger = $logger;
    }

    public function initialize($exchange_name)
    {
        if (!$exchange_name) {
            throw new RuntimeError('Invalid "exchange_name" for JobService initialize call.');
        }

        $wait_exchange_name = $exchange_name . self::WAIT_SUFFIX;
        $wait_queue_name = $wait_exchange_name . self::QUEUE_SUFFIX;
        $unrouted_exchange_name = $exchange_name . self::UNROUTED_SUFFIX;
        $unrouted_queue_name = $unrouted_exchange_name . self::QUEUE_SUFFIX;
        $repub_exchange_name = $exchange_name . self::REPUB_SUFFIX;
        $repub_queue_name = $repub_exchange_name . self::QUEUE_SUFFIX;

        $this->channel = $this->connector->getConnection()->channel();
        $this->channel->exchange_declare($unrouted_exchange_name, 'fanout', false, true, false, true); //internal
        $this->channel->exchange_declare($repub_exchange_name, 'fanout', false, true, false, true); //internal
        $this->channel->exchange_declare($wait_exchange_name, 'fanout', false, true, false);
        $this->channel->exchange_declare($exchange_name, 'direct', false, true, false, false, false, [
            'alternate-exchange' => [ 'S', $unrouted_exchange_name ]
        ]);
        $this->channel->queue_declare($wait_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $exchange_name ]
        ]);
        $this->channel->queue_bind($wait_queue_name, $wait_exchange_name);
        $this->channel->queue_declare($unrouted_queue_name, false, true, false, false, false, [
            'x-dead-letter-exchange' => [ 'S', $repub_exchange_name ],
            'x-message-ttl' => [ 'I', self::REPUB_INTERVAL ]
        ]);
        $this->channel->queue_bind($unrouted_queue_name, $unrouted_exchange_name);
        $this->channel->queue_declare($repub_queue_name, false, true, false, false);
        $this->channel->queue_bind($repub_queue_name, $repub_exchange_name);

        // the following hacky stuff is to create a shovel to republish unrouted messages back to
        // the exchange. ideally we should move this to an admin setup procedure.
        $config = $this->connector->getConfig();

        $url = sprintf(
            '%s://%s:15672/api/parameters/shovel/%%2f/%s.shovel',
            $config->get('transport', 'http'),
            $config->get('host'),
            $repub_exchange_name
        );

        $body = [
            'value' => [
                'src-uri' => 'amqp://',
                'src-queue' => $repub_queue_name,
                'dest-uri' => 'amqp://',
                'dest-exchange' => $exchange_name,
                'add-forward-headers' => false,
                'ack-mode' => 'on-confirm',
                'delete-after' => 'never'
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'content-type:application/json' ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $config->get('user') . ':' . $config->get('password'));
        curl_exec($ch);
        curl_close($ch);
    }

    public function initializeQueue($exchange_name, $queue_name, $routing_key)
    {
        if (!$exchange_name) {
            throw new RuntimeError('Invalid "exchange_name" for JobService initializeQueue call.');
        }

        if (!$queue_name) {
            throw new RuntimeError('Invalid "queue_name" for JobService initializeQueue call.');
        }

        if (!$routing_key) {
            throw new RuntimeError('Invalid "routing_key" for JobService initializeQueue call.');
        }

        $this->channel->queue_declare($queue_name, false, true, false, false);
        $this->channel->queue_bind($queue_name, $exchange_name, $routing_key);
    }

    public function dispatch(JobInterface $job, $exchange_name)
    {
        $message_payload = json_encode($job->toArray());
        $message = new AMQPMessage($message_payload, [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]);

        $routing_key = $job->getSettings()->get('routing_key');
        $this->channel->basic_publish($message, $exchange_name, $routing_key);
    }

    public function consume($queue_name, Closure $message_callback)
    {
        if (!$this->channel) {
            throw new RuntimeError('Channel has not been initialized prior to consumption.');
        }

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queue_name, false, true, false, false, false, $message_callback);

        return $this->channel;
    }

    public function retry(JobInterface $job, $exchange_name)
    {
        $job_state = $job->toArray();
        $job_state['metadata']['retries'] = isset($job_state['metadata']['retries'])
            ? ++$job_state['metadata']['retries'] : 1;

        $message = new AMQPMessage(
            json_encode($job_state),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'expiration' => $job->getStrategy()->getRetryInterval()
            ]
        );

        $routing_key = $job->getSettings()->get('routing_key');
        $this->channel->basic_publish($message, $exchange_name, $routing_key);
    }

    public function fail(JobInterface $job, $exchange_name, Exception $error)
    {
        $failed_job = $this->createJob(
            [
                'event' => new FailedJobEvent([
                    'failed_job_state' => $job->toArray(),
                    'metadata' => [
                        'error_message' => $error->getMessage(),
                        'error_trace' => $error->getTraceAsString()
                    ]
                ]),
                'channel' => ChannelMap::CHANNEL_FAILED
            ]
        );

        $message = new AMQPMessage(
            json_encode($failed_job->toArray()),
            [ 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT ]
        );

        $routing_key = $job->getSettings()->get('routing_key');
        $this->channel->basic_publish($message, $exchange_name, $routing_key);
    }

    public function createJob(array $job_state, $job_name = self::DEFAULT_JOB)
    {
        $job_config = $this->getJob($job_name);
        $strategy_config = $job_config['strategy'];
        $service_locator = $this->service_locator;

        $strategy_callback = function (JobInterface $job) use ($service_locator, $strategy_config) {
            $strategy_implementor = $strategy_config['implementor'];

            $retry_strategy = $service_locator->createEntity(
                $strategy_config['retry']['implementor'],
                [ ':job' => $job, ':settings' => $strategy_config['retry']['settings'] ]
            );

            $failure_strategy = $service_locator->createEntity(
                $strategy_config['failure']['implementor'],
                [ ':job' => $job, ':settings' => $strategy_config['failure']['settings'] ]
            );

            return new $strategy_implementor($retry_strategy, $failure_strategy);
        };

        return $this->service_locator->createEntity(
            $job_config['class'],
            [
                // job class cannot be overridden by state
                ':state' => [ Job::OBJECT_TYPE => $job_config['class'] ] + $job_state,
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
}

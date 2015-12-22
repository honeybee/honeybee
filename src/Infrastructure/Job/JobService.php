<?php

namespace Honeybee\Infrastructure\Job;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\DataAccess\Connector\RabbitMqConnector;
use PhpAmqpLib\Message\AMQPMessage;
use Honeybee\ServiceLocatorInterface;
use Honeybee\Common\Error\RuntimeError;
use Psr\Log\LoggerInterface;

class JobService implements JobServiceInterface
{
    protected $connector;

    protected $config;

    protected $logger;

    protected $service_locator;

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

    public function dispatch(JobInterface $job, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;

        $exchange_name = $settings->get('exchange', $this->config->get('exchange'));
        if (!$exchange_name) {
            throw new RuntimeError('Missing required "exchange" setting for JobService dispatch call.');
        }

        if (!$this->channel) {
            $this->channel = $this->connector->getConnection()->channel();
            $this->channel->exchange_declare($exchange_name, 'direct', false, true, false);
        }

        $message_payload = json_encode($job->toArray());
        $message = new AMQPMessage($message_payload, [ 'delivery_mode' => 2 ]);

        $this->channel->basic_publish($message, $exchange_name, $settings->get('route_key', 'default'));
    }

    public function createJob(array $job_state)
    {
        $job_class = $job_state['@type'];

        if (!class_exists($job_class)) {
            throw new RuntimeError("Unable to resolve job implementor: " . $job_class);
        }

        return $this->service_locator->createEntity($job_class, array(':state' => $job_state));
    }
}

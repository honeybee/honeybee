<?php

namespace Honeybee\Infrastructure\Job;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\ServiceLocatorInterface;
use Psr\Log\LoggerInterface;

abstract class JobService implements JobServiceInterface
{
    protected $serviceLocator;

    protected $eventBus;

    protected $jobMap;

    protected $config;

    protected $logger;

    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        EventBusInterface $eventBus,
        JobMap $jobMap,
        ConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->eventBus = $eventBus;
        $this->jobMap = $jobMap;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getJobMap()
    {
        return $this->jobMap;
    }

    public function getJob($jobName)
    {
        $jobConfig = $this->jobMap->get($jobName);

        if (!$jobConfig) {
            throw new RuntimeError(sprintf('Configuration for job "%s" was not found.', $jobName));
        }

        return $jobConfig;
    }

    /**
     * @todo Job building and JobMap should be provisioned and injected where required, and
     * so the following public methods are not specified on the interface.
     */
    public function createJob(array $jobState)
    {
        if (!isset($jobState['metadata']['job_name']) || empty($jobState['metadata']['job_name'])) {
            throw new RuntimeError('Unable to get job name from metadata.');
        }

        $jobName = $jobState['metadata']['job_name'];

        $jobConfig = $this->getJob($jobName);
        $strategyConfig = $jobConfig['strategy'];
        $serviceLocator = $this->serviceLocator;

        $strategyCallback = function (JobInterface $job) use ($serviceLocator, $strategyConfig) {
            $strategyImplementor = $strategyConfig['implementor'];

            $retryStrategy = $serviceLocator->make(
                $strategyConfig['retry']['implementor'],
                [':job' => $job, ':settings' => $strategyConfig['retry']['settings']]
            );

            $failureStrategy = $serviceLocator->make(
                $strategyConfig['failure']['implementor'],
                [':job' => $job, ':settings' => $strategyConfig['failure']['settings']]
            );

            return new $strategyImplementor($retryStrategy, $failureStrategy);
        };

        return $this->serviceLocator->make(
            $jobConfig['class'],
            [
                // job class cannot be overridden by state
                ':state' => ['@type' => $jobConfig['class']] + $jobState,
                ':strategy_callback' => $strategyCallback,
                ':settings' => $jobConfig['settings']
            ]
        );
    }
}

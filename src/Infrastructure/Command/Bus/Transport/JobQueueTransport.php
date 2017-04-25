<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Assert\Assertion;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Job\Bundle\ExecuteCommandJob;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends CommandTransport
{
    protected $job_service;

    protected $exchange;

    public function __construct($name, JobServiceInterface $job_service, $exchange)
    {
        Assertion::string($exchange);
        parent::__construct($name);
        $this->job_service = $job_service;
        $this->exchange = $exchange;
    }

    public function send(CommandInterface $command, SettingsInterface $settings = null)
    {
        $settings = $settings ?: new Settings;
        $job_name = $settings->get('job');

        Assertion::string($job_name);
        Assertion::notEmpty($job_name);

        $job = $this->job_service->createJob([
            'command' => $command,
            'metadata' => [ 'job_name' => $job_name ]
        ]);
        $this->job_service->dispatch($job, $this->exchange);
    }
}

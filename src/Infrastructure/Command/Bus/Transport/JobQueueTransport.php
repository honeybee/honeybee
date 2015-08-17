<?php

namespace Honeybee\Infrastructure\Command\Bus\Transport;

use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandInterface;
use Honeybee\Infrastructure\Config\Settings;
use Honeybee\Infrastructure\Job\Bundle\ExecuteCommandJob;
use Honeybee\Infrastructure\Job\JobServiceInterface;

class JobQueueTransport extends CommandTransport
{
    protected $exchange;

    protected $job_service;

    protected $command_bus;

    public function __construct($name, $exchange, JobServiceInterface $job_service, CommandBusInterface $command_bus)
    {
        parent::__construct($name);

        $this->exchange = $exchange;
        $this->job_service = $job_service;
        $this->command_bus = $command_bus;
    }

    public function send(CommandInterface $command)
    {
        $this->job_service->dispatch(
            new ExecuteCommandJob(
                $this->command_bus,
                array('command' => $command)
            ),
            new Settings(
                array(
                    'route_key' => $command->getType(),
                    'exchange' => $this->exchange
                )
            )
        );
    }
}

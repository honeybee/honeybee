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

    protected $queue_name;

    protected $job_service;

    protected $command_bus;

    public function __construct(
        $name,
        $exchange,
        JobServiceInterface $job_service,
        CommandBusInterface $command_bus,
        $queue_name = null
    ) {
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
                [ 'command' => $command ]
            ),
            new Settings(
                [
                    'routing_key' => $this->queue_name ?: $command->getType(),
                    'exchange' => $this->exchange
                ]
            )
        );
    }
}

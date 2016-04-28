<?php

namespace Honeybee\Infrastructure\ProcessManager;

use DateTime;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Command\CommandList;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;
use Shrink0r\Monatic\Maybe;

class ProcessManager implements ProcessManagerInterface
{
    protected $config;

    protected $process_map;

    protected $data_access_service;

    protected $command_bus;

    protected $logger;

    public function __construct(
        ConfigInterface $config,
        ProcessMap $process_map,
        DataAccessServiceInterface $data_access_service,
        CommandBusInterface $command_bus,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->process_map = $process_map;
        $this->data_access_service = $data_access_service;
        $this->command_bus = $command_bus;
        $this->logger = $logger;

        if (!$this->config->has('storage_reader')) {
            throw new RuntimeError('Missing setting for "storage_reader" within ProcessManager config.');
        }
        if (!$this->config->has('storage_writer')) {
            throw new RuntimeError('Missing setting for "storage_writer" within ProcessManager config.');
        }
    }

    public function hasCompleted(ProcessStateInterface $process_state)
    {
        $process = $this->process_map->getByName($process_state->getProcessName());

        return $process->hasFinished($process_state);
    }

    public function beginProcess(ProcessStateInterface $process_state, EventInterface $event = null)
    {
        $process = $this->process_map->getByName($process_state->getProcessName());
        if (!$process->hasStarted($process_state)) {
            $this->runProcess($process_state, $event);
        } else {
            throw new RuntimeError('Process has already started and may not not be started again.');
        }

        return $process_state;
    }

    public function continueProcess(EventInterface $event)
    {
        $process_state = $this->loadProcessStateBy($event);
        if ($process_state) {
            $this->runProcess($process_state, $event);
        } else {
            throw new RuntimeError('Unable to find process for given event: ' . $event->getType());
        }

        return $process_state;
    }

    protected function runProcess(ProcessStateInterface $process_state, EventInterface $event = null)
    {
        $process = $this->process_map->getByName($process_state->getProcessName());
        if (!$process->hasFinished($process_state)) {
            $commands = $process->proceed($process_state, $event);
            $this->persistProcessState($process_state);
            if ($commands instanceof CommandList && !$commands->isEmpty()) {
                foreach ($commands as $command) {
                    $this->command_bus->post($command);
                }
            }
        } else {
            throw new RuntimeError('The given process has already completed and may not be run again.');
        }
        if ($this->hasCompleted(($process_state))) {
            $this->purgeProcessState($process_state);
        }
    }

    protected function loadProcessStateBy(EventInterface $event)
    {
        $metadata = Maybe::unit($event->getMetadata());
        $process_uuid = $metadata->process_uuid->get();
        $process_name = $metadata->process_name->get();
        $process_state_reader = $this->config->get('storage_reader');
        $process_state = null;

        if ($process_uuid && $process_name) {
            $process_state = $this->data_access_service->readFrom($process_state_reader, $process_uuid);
            if (!$process_state || $process_state->getProcessName() !== $process_name) {
                throw new RuntimeError(
                    sprintf(
                        'Given process name "%s" coming from event metadata does not match the loaded one: "%s"',
                        $process_name,
                        $process_state->getProcessName()
                    )
                );
            }
        }

        return $process_state;
    }

    protected function persistProcessState(ProcessStateInterface $process_state)
    {
        $process_state_writer = $this->config->get('storage_writer');

        return $this->data_access_service->writeTo($process_state_writer, $process_state);
    }

    protected function purgeProcessState(ProcessStateInterface $process_state)
    {
        $process_state_writer = $this->config->get('storage_writer');

        return $this->data_access_service->deleteFrom($process_state_writer, $process_state->getUuid());
    }
}

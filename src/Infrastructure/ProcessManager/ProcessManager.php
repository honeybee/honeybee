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

    protected $stack_depth = 0;

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
        $process = $this->process_map->getItem($process_state->getProcessName());

        return $process->hasFinished($process_state);
    }

    public function beginProcess(ProcessStateInterface $process_state, EventInterface $event = null)
    {
        $process = $this->process_map->getItem($process_state->getProcessName());
        if ($process->hasStarted($process_state)) {
            throw new RuntimeError('Process has already started and may not not be started again.');
        }

        return $this->runProcess($process_state, $event);
    }

    public function continueProcess(EventInterface $event)
    {
        $process_state = $this->loadProcessStateBy($event);
        if (!$process_state) {
            throw new RuntimeError('Unable to find process for given event: ' . $event->getType());
        }

        return $this->runProcess($process_state, $event);
    }

    protected function runProcess(ProcessStateInterface $process_state, EventInterface $event = null)
    {
        $process = $this->process_map->getItem($process_state->getProcessName());
        if (!$process->hasFinished($process_state)) {
            $commands = $process->proceed($process_state, $event);
            $this->persistProcessState($process_state);
            $this->stack_depth++;
            if ($commands instanceof CommandList && !$commands->isEmpty()) {
                foreach ($commands as $command) {
                    $this->command_bus->post($command);
                }
            }
            $this->stack_depth--;
        } else {
            throw new RuntimeError('The given process has already completed and may not be run again.');
        }

        if ($this->hasCompleted(($process_state)) && $this->stack_depth === 0) {
            $resulting_process_state = $this->purgeProcessState($process_state->getUuid());
        } else {
            $resulting_process_state = $this->readProcessState($process_state->getUuid());
        }

        return $resulting_process_state;
    }

    protected function loadProcessStateBy(EventInterface $event)
    {
        $metadata = Maybe::unit($event->getMetadata());
        $process_uuid = $metadata->process_uuid->get();
        $process_name = $metadata->process_name->get();

        if ($process_uuid && $process_name) {
            $process_state = $this->readProcessState($process_uuid);
            if (!$process_state || $process_state->getProcessName() !== $process_name) {
                throw new RuntimeError(
                    sprintf(
                        'Given process name "%s" coming from event metadata does not match the loaded one: "%s"',
                        $process_name,
                        $process_state->getProcessName()
                    )
                );
            }
        } else {
            $process_state = null;
        }

        return $process_state;
    }

    protected function readProcessState($process_uuid)
    {
        $cached_state = $this->data_access_service->readFrom($this->config->get('cache_reader'), $process_uuid);

        if (!$cached_state) {
            $process_state = $this->data_access_service->readFrom($this->config->get('storage_reader'), $process_uuid);
        } else {
            $process_state = $cached_state;
        }

        return $process_state;
    }

    protected function cacheProcessState(ProcessStateInterface $process_state)
    {
        return $this->data_access_service->writeTo($this->config->get('cache_writer'), $process_state);
    }

    protected function persistProcessState(ProcessStateInterface $process_state)
    {
        $this->cacheProcessState($process_state);

        return $this->data_access_service->writeTo($this->config->get('storage_writer'), $process_state);
    }

    protected function purgeProcessState($process_uuid)
    {
        if ($process_state = $this->readProcessState($process_uuid)) {
            $this->data_access_service->deleteFrom($this->config->get('cache_writer'), $process_uuid);
            $this->data_access_service->deleteFrom($this->config->get('storage_writer'), $process_uuid);
        }

        return $process_state;
    }
}

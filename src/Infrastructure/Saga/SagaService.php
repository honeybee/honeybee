<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Psr\Log\LoggerInterface;
use Shrink0r\Monatic\Maybe;

class SagaService implements SagaServiceInterface
{
    protected $config;

    protected $saga_map;

    protected $data_access_service;

    protected $command_bus;

    protected $logger;

    public function __construct(
        ConfigInterface $config,
        SagaMap $saga_map,
        DataAccessServiceInterface $data_access_service,
        CommandBusInterface $command_bus,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->saga_map = $saga_map;
        $this->data_access_service = $data_access_service;
        $this->command_bus = $command_bus;
        $this->logger = $logger;

        if (!$this->config->has('storage_reader')) {
            throw new RuntimeError('Missing setting for "storage_reader" within SagaService config.');
        }
        if (!$this->config->has('storage_writer')) {
            throw new RuntimeError('Missing setting for "storage_writer" within SagaService config.');
        }
    }

    public function beginSaga(SagaSubjectInterface $saga_subject)
    {
        $saga = $this->saga_map->getBySubject($saga_subject);
        if (!$saga->hasStarted($saga_subject)) {
            $command = $saga->proceed($saga_subject);
            $this->persistSagaSubject($saga_subject);
            $this->command_bus->post($command);
        } else {
            $this->logger->debug(__METHOD__ . ' - Saga has allready started!');
        }
    }

    public function continueSaga(EventInterface $event = null)
    {
        $saga_subject = $this->loadSagaSubjectBy($event);
        if ($saga_subject) {
            $saga = $this->saga_map->getBySubject($saga_subject);
            if (!$saga->hasFinished($saga_subject)) {
                $command = $saga->proceed($saga_subject, $event->getType());
                $this->persistSagaSubject($saga_subject);
                if ($command) {
                     $this->command_bus->post($command);
                 }
            } else {
                $this->logger->debug(__METHOD__ . ' - Saga has allready completed!');
            }
        } else {
            $this->logger->debug(__METHOD__ . ' - No saga found for given event.');
        }
    }

    protected function loadSagaSubjectBy(EventInterface $event)
    {
        $meta_data = Maybe::unit($event->getMetaData());
        $saga_uuid = $meta_data->saga_uuid->get();
        $saga_name = $meta_data->saga_name->get();
        $saga_subject_reader = $this->config->get('storage_reader');

        if ($saga_uuid && $saga_name) {
            return $this->data_access_service->readFrom($saga_subject_reader, $saga_uuid);
        }

        return null;
    }

    protected function persistSagaSubject(SagaSubjectInterface $saga_subject)
    {
        $saga_subject_writer = $this->config->get('storage_writer');

        return $this->data_access_service->writeTo($saga_subject_writer, $saga_subject);
    }
}

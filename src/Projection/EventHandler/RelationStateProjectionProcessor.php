<?php

namespace Honeybee\Projection\EventHandler;

use Honeybee\Infrastructure\DataAccess\Storage\StorageWriterMap;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\Event\EventInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessManagerInterface;
use Honeybee\Infrastructure\ProcessManager\ProcessState;
use Honeybee\Projection\ProjectionTypeMap;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Task\ProceedWorkflow\WorkflowProceededEvent;
use Psr\Log\LoggerInterface;

class RelationStateProjectionProcessor extends RelationProjectionUpdater
{
    protected $process_manager;

    public function __construct(
        ConfigInterface $config,
        LoggerInterface $logger,
        StorageWriterMap $storage_writer_map,
        QueryServiceMap $query_service_map,
        ProjectionTypeMap $projection_type_map,
        AggregateRootTypeMap $aggregate_root_type_map,
        ProcessManagerInterface $process_manager
    ) {
        parent::__construct(
            $config,
            $logger,
            $storage_writer_map,
            $query_service_map,
            $projection_type_map,
            $aggregate_root_type_map
        );

        $this->process_manager = $process_manager;
    }

    public function handleEvent(EventInterface $event)
    {
        $affected_entities = parent::handleEvent($event);

        if ($event instanceof WorkflowProceededEvent
            && !$affected_entities->isEmpty()
        ) {
            // @todo add the origin type_prefix to the payload so we can
            // decide the relation state transition based on the origin type
            $payload = [ 'origin_state' => $event->getWorkflowState() ];
            foreach ($affected_entities as $affected_entity) {
               $payload[ 'affected_entities' ][] = [
                   'identifier' => $affected_entity->getIdentifier(),
                   'revision' => $affected_entity->getRevision(),
                   'state' => $affected_entity->getWorkflowState(),
                   'type_prefix' => $affected_entity->getType()->getPrefix()
               ];
            }

            $process_state = new ProcessState(
                [
                    'process_name' => $this->config->get('process_name'),
                    'payload' => $payload
                ]
            );

            if ($this->config->get('pass_event', false)) {
                $this->process_manager->beginProcess($process_state, $event);
            } else {
                $this->process_manager->beginProcess($process_state);
            }
        }
    }
}

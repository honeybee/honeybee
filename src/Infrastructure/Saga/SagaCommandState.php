<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use JmesPath\AstRuntime;
use Workflux\State\State;
use Workflux\StatefulSubjectInterface;

class SagaCommandState extends State
{
    public function __construct(
        $name,
        $type = self::TYPE_ACTIVE,
        array $options = [],
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        parent::__construct($name, $type, $options);

        $this->aggregate_root_type_map = $aggregate_root_type_map;
    }

    public function onEntry(StatefulSubjectInterface $saga_subject)
    {
        if (!$saga_subject instanceof SagaSubjectInterface) {
            throw new RuntimeError('Only ' . SagaSubjectInterface::CLASS . ' subjects supported by ' . static::CLASS);
        }

        parent::onEntry($saga_subject);

        $command = $this->createCommand($saga_subject);
        $execution_context = $saga_subject->getExecutionContext();
        $execution_context->setParameter('command', $command);
    }

    protected function createCommand(SagaSubjectInterface $saga_subject)
    {
        $command_class = $this->getCommandImplementor($saga_subject);
        $aggregate_root_type = $this->getAggregateRootType();

        return new $command_class(
            [
                'aggregate_root_type' => get_class($aggregate_root_type),
                'values' => $this->getCommandPayload($saga_subject),
                'meta_data' => [
                    'saga_name' => $saga_subject->getSagaName(),
                    'saga_uuid' => $saga_subject->getUuid()
                ]
            ]
        );
    }

    protected function getCommandPayload(SagaSubjectInterface $saga_subject)
    {
        $command_class = $this->getCommandImplementor();
        $payload = $saga_subject->getPayload();
        $payload_path = $this->options->get('payload_path');
        $jmes_path_runtime = new AstRuntime();

        return $jmes_path_runtime($payload_path, $payload);
    }

    protected function getCommandImplementor()
    {
        $command_class = $this->options->get('command');
        if (!$command_class) {
            throw new RuntimeError('Missing required "command_class" option.');
        }
        if (!class_exists($command_class)) {
            throw new RuntimeError('Unable to load configured command class: ' . $command_class);
        }

        return $command_class;
    }

    protected function getAggregateRootType()
    {
        $ar_type_prefix = $this->options->get('aggregate_root_type');
        if (!$ar_type_prefix) {
            throw new RuntimeError('Missing require options "aggregate_root_type"');
        }
        if (!$this->aggregate_root_type_map->hasKey($ar_type_prefix)) {
            throw new RuntimeError('Unable to resolve given ar-prefix: ' . $ar_type_prefix);
        }

        return $this->aggregate_root_type_map->getItem($ar_type_prefix);
    }
}

<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Workflux\StatefulSubjectInterface;

class ModifyAggregateRootStateNode extends AggregateRootCommandStateNode
{
    public function __construct(
        $name,
        $type = self::TYPE_ACTIVE,
        array $options = [],
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        parent::__construct($name, $type, $options, $aggregate_root_type_map);

        $this->needs('projection_key');
    }

    public function onEntry(StatefulSubjectInterface $process_state)
    {
        $this->requiresVariable($this->options->get('projection_key'), $process_state);
    }

    public function onExit(StatefulSubjectInterface $process_state)
    {
        parent::onExit($process_state);

        if ($this->options->has('export_as_reference')) {
            $projection = $this->getProjection($process_state);

            $export_as_reference = $this->options->get('export_as_reference');
            $embed_type = $export_as_reference->get('reference_embed_type');
            $export_to = $export_as_reference->get('export_to');

            $payload = $process_state->getPayload();
            $payload[$export_to] = [
                '@type' => $embed_type,
                'referenced_identifier' => $projection->getIdentifier()
            ];

            $execution_context = $process_state->getExecutionContext();
            $execution_context->setParameter('payload', $payload);
        }
    }

    protected function createCommand(ProcessStateInterface $process_state)
    {
        $command_class = $this->getCommandImplementor($process_state);
        $aggregate_root_type = $this->getAggregateRootType();
        $projection = $this->getProjection($process_state);

        return new $command_class(
            [
                'aggregate_root_type' => get_class($aggregate_root_type),
                'aggregate_root_identifier' => $projection->getIdentifier(),
                'known_revision' => $projection->getRevision(),
                'values' => $this->getCommandPayload($process_state),
                'meta_data' => [
                    'process_name' => $process_state->getProcessName(),
                    'process_uuid' => $process_state->getUuid()
                ],
                'embedded_entity_commands' => array_merge(
                    $this->buildReferenceCommands($process_state),
                    $this->buildEmbedCommands($process_state)
                )
            ]
        );
    }

    protected function buildReferenceCommands(ProcessStateInterface $process_state)
    {
        $payload = $process_state->getPayload();
        $projection = $this->getProjection($process_state);

        $reference_commands = [];
        foreach ((array)$this->options->get('link_relations', []) as $reference_attribute_name => $payload_key) {
            if (isset($payload[$payload_key])) {
                $relation_payload = $payload[$payload_key];
                $referenced_identifier = $relation_payload['referenced_identifier'];

                $embeds_to_remove = [];
                $reference_exists = false;
                foreach ($projection->getValue($reference_attribute_name) as $reference_embed) {
                    $embeds_to_remove[$reference_attribute_name] = [];
                    if ($reference_embed->getIdentifier() === $referenced_identifier) {
                        $reference_exists = true;
                        continue;
                    } else {
                        $embeds_to_remove[$reference_attribute_name] = $reference_embed;
                    }
                }

                foreach ($embeds_to_remove as $attribute_name => $embeds_to_remove) {
                    foreach ($embeds_to_remove as $embed_to_remove) {
                        $reference_commands[] = new RemoveEmbeddedEntityCommand(
                            [
                                'embedded_entity_identifier' => $embed_to_remove->getIdentifier(),
                                'embedded_entity_type' => $embed_to_remove->getType()->getPrefix(),
                                'parent_attribute_name' => $attribute_name
                            ]
                        );
                    }
                }

                if (!$reference_exists) {
                    $embedded_entity_type = $relation_payload['@type'];
                    unset($relation_payload['@type']);
                    $reference_commands[] = new AddEmbeddedEntityCommand(
                        [
                            'embedded_entity_type' => $embedded_entity_type,
                            'parent_attribute_name' => $reference_attribute_name,
                            'position' => 0,
                            'values' => $relation_payload
                        ]
                    );
                }
            }
        }

        return $reference_commands;
    }

    protected function buildEmbedCommands(ProcessStateInterface $process_state)
    {

        return [];
    }

    protected function getProjection(ProcessStateInterface $process_state)
    {
        return $process_state->getExecutionContext()->getParameter(
            $this->options->get('projection_key')
        );
    }
}

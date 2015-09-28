<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Infrastructure\Event\NoOpSignal;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Workflux\StatefulSubjectInterface;

class ModifyAggregateRootStateNode extends AggregateRootCommandStateNode
{
    public function onExit(StatefulSubjectInterface $process_state)
    {
        parent::onExit($process_state);

        $export_key = null;
        $execution_context = $process_state->getExecutionContext();
        $incoming_event = $execution_context->getParameter('incoming_event');
        if ($incoming_event instanceof NoOpSignal) {
            $command_data = $incoming_event->getCommandData();
            $aggregate_root_identifier = $command_data['aggregate_root_identifier'];
        } else {
            $aggregate_root_identifier = $incoming_event->getAggregateRootIdentifier();
        }
        if ($this->options->has('export_as_reference')) {
            $export_as_reference = $this->options->get('export_as_reference');
            $embed_type = $export_as_reference->get('reference_embed_type');
            $export_key = $export_as_reference->get('export_to');

            $reference_data = [
                [
                    '@type' => $embed_type,
                    'referenced_identifier' => $aggregate_root_identifier
                ]
            ];

            $execution_context->setParameter($export_key, $reference_data);
        }

        $projection_key = $this->options->get('projection_key');
        if ($projection_key !== $export_key) {
            $execution_context->removeParameter($projection_key);
        }
    }

    protected function createCommand(ProcessStateInterface $process_state)
    {
        $this->needs('projection_key');

        $command_class = $this->getCommandImplementor($process_state);
        $aggregate_root_type = $this->getAggregateRootType();
        $projection = $this->getProjection($process_state);
        $command_payload = $this->getCommandPayload($process_state);

        return new $command_class(
            [
                'aggregate_root_type' => get_class($aggregate_root_type),
                'aggregate_root_identifier' => $projection->getIdentifier(),
                'known_revision' => $projection->getRevision(),
                'values' => $command_payload,
                'meta_data' => [
                    'process_name' => $process_state->getProcessName(),
                    'process_uuid' => $process_state->getUuid()
                ],
                'embedded_entity_commands' => $this->buildEmbedCommandList($process_state, $command_payload)
            ]
        );
    }

    protected function buildEmbedCommandList(ProcessStateInterface $process_state, $command_payload = [])
    {
        return array_merge(
            $this->buildReferenceCommands($process_state, $process_state->getPayload()),
            $this->buildEmbedCommands($process_state, $command_payload)
        );
    }

    protected function buildReferenceCommands(ProcessStateInterface $process_state, array $payload)
    {
        $projection = $this->getProjection($process_state);
        $reference_commands = [];
        foreach ((array)$this->options->get('link_relations', []) as $reference_attribute_name => $payload_key) {
            $relation_payload = isset($payload[$payload_key]) ? $payload[$payload_key][0] : null;
            if (!$relation_payload) {
                continue;
            }

            $referenced_identifier = $relation_payload['referenced_identifier'];

            $embeds_to_remove = [];
            $reference_exists = false;
            foreach ($projection->getValue($reference_attribute_name) as $reference_embed) {
                $embeds_to_remove[$reference_attribute_name] = [];
                if ($reference_embed->getReferencedIdentifier() !== $referenced_identifier) {
                    $reference_commands[] = new RemoveEmbeddedEntityCommand(
                        [
                            'embedded_entity_identifier' => $reference_embed->getIdentifier(),
                            'embedded_entity_type' => $reference_embed->getType()->getPrefix(),
                            'parent_attribute_name' => $reference_attribute_name
                        ]
                    );
                } else {
                    $reference_exists = true;
                    continue;
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

        return $reference_commands;
    }

    protected function buildEmbedCommands(ProcessStateInterface $process_state, array $payload)
    {
        $aggregate_root_type = $this->getAggregateRootType();
        $projection = $this->getProjection($process_state);
        $embed_attributes = $aggregate_root_type->getAttributes()->filter(
            function($attribute) {
                return $attribute instanceof EmbeddedEntityListAttribute
                    && !$attribute instanceof EntityReferenceListAttribute;
            }
        );

        $embed_commands = [];
        foreach ($embed_attributes as $embed_attribute_name => $embed_attribute) {
            if (isset($payload[$embed_attribute_name])) {
                $embedded_entity_list = $projection->getValue($embed_attribute_name);
                foreach ($payload[$embed_attribute_name] as $pos => $embed_data) {
                    $embed_type = $embed_data['@type'];
                    unset($embed_data['@type']);

                    $embedded_entity = $embedded_entity_list->getItem($pos);
                    if (!$embedded_entity) {
                        $embed_commands[] = new AddEmbeddedEntityCommand(
                            [
                                'embedded_entity_type' => $embed_type,
                                'parent_attribute_name' => $embed_attribute_name,
                                'values' => $embed_data,
                                'position' => $pos
                            ]
                        );
                        continue;
                    }

                    if ($embedded_entity->getType()->getPrefix() !== $embed_type) {
                        $embed_commands[] = new RemoveEmbeddedEntityCommand(
                            [
                                'embedded_entity_type' => $embed_type,
                                'parent_attribute_name' => $embed_attribute_name,
                                'embedded_entity_identifier' => $embedded_entity->getIdentifier()
                            ]
                        );
                        continue;
                    }

                    $modified_data = [];
                    foreach ($embed_data as $key => $value) {
                        $value_holder = $embedded_entity->getType()->getAttribute($key)->createValueHolder();
                        $result = $value_holder->setValue($value, $embedded_entity);
                        if ($result->getSeverity() >= IncidentInterface::NOTICE) {
                            if (!$value_holder->sameValueAs($embedded_entity->getValue($key))) {
                                $modified_data[$key] = $value_holder->toNative();
                            }
                        } else {
                            error_log(__METHOD__ . " - Invalid embed-data given.");
                        }
                    }
                    if (!empty($modified_data)) {
                        $embed_commands[] = new ModifyEmbeddedEntityCommand(
                            [
                                'embedded_entity_type' => $embed_type,
                                'parent_attribute_name' => $embed_attribute_name,
                                'embedded_entity_identifier' => $embedded_entity->getIdentifier(),
                                'values' => $modified_data
                            ]
                        );
                    }
                }
            }
        }

        return $embed_commands;
    }

    protected function getProjection(ProcessStateInterface $process_state)
    {
        return $process_state->getExecutionContext()->getParameter(
            $this->options->get('projection_key')
        );
    }
}

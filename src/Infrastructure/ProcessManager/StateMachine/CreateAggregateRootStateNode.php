<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Workflux\StatefulSubjectInterface;

class CreateAggregateRootStateNode extends AggregateRootCommandStateNode
{
    public function onExit(StatefulSubjectInterface $process_state)
    {
        parent::onExit($process_state);

        $this->requiresVariable('incoming_event', $process_state);
        $execution_context = $process_state->getExecutionContext();
        $export_as_reference = $this->options->get('export_as_reference', false);

        if ($export_as_reference) {
            $event = $execution_context->getParameter('incoming_event');
            $export_key = $export_as_reference->get('export_to');
            $reference_data = [
                [
                    '@type' => $export_as_reference->get('reference_embed_type'),
                    'referenced_identifier' => $event->getAggregateRootIdentifier()
                ]
            ];
            $execution_context->setParameter($export_key, $reference_data);
        }
    }

    protected function createCommand(ProcessStateInterface $process_state)
    {
        $command_class = $this->getCommandImplementor($process_state);
        $aggregate_root_type = $this->getAggregateRootType();
        $command_payload = $this->getCommandPayload($process_state);

        return new $command_class(
            [
                'aggregate_root_type' => get_class($aggregate_root_type),
                'values' => $command_payload,
                'embedded_entity_commands' => $this->buildEmbedCommandList($process_state, $command_payload),
                'meta_data' => [
                    'process_name' => $process_state->getProcessName(),
                    'process_uuid' => $process_state->getUuid()
                ]
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
        $buildCommand = function ($type, $attribute, $position, $cmd_payload) {
            return new AddEmbeddedEntityCommand([
                'embedded_entity_type' => $type,
                'parent_attribute_name' => $attribute,
                'position' => $position,
                'values' => $cmd_payload
            ]);
        };

        $reference_commands = [];
        foreach ((array)$this->options->get('link_relations', []) as $attribute_name => $payload_key) {
            $pos = 0;
            if (!is_string($payload_key)) { // dealing with a params instance
                foreach ((array)$payload_key as $reference_key) {
                    $command_values = $payload[$reference_key][0];
                    $reference_type = $command_values['@type'];
                    unset($command_values['@type']);
                    $reference_commands[] = $buildCommand($reference_type, $attribute_name, $pos++, $command_values);
                }
            } elseif (isset($payload[$payload_key])) {
                $command_values = $payload[$payload_key][0];
                $reference_type = $command_values['@type'];
                unset($command_values['@type']);
                $reference_commands[] = $buildCommand($reference_type, $attribute_name, $pos++, $command_values);
            }
        }

        return $reference_commands;
    }

    protected function buildEmbedCommands(ProcessStateInterface $process_state, array $payload)
    {
        $aggregate_root_type = $this->getAggregateRootType();
        $embed_attributes = $aggregate_root_type->getAttributes()->filter(
            function ($attribute) {
                return $attribute instanceof EmbeddedEntityListAttribute
                    && !$attribute instanceof EntityReferenceListAttribute;
            }
        );
        $embed_commands = [];
        foreach ($embed_attributes as $embed_attribute_name => $embed_attribute) {
            if (isset($payload[$embed_attribute_name])) {
                foreach ($payload[$embed_attribute_name] as $embed_data) {
                    $embed_type = $embed_data['@type'];
                    unset($embed_data['@type']);
                    $embed_commands[] = new AddEmbeddedEntityCommand(
                        [
                            'embedded_entity_type' => $embed_type,
                            'parent_attribute_name' => $embed_attribute_name,
                            'values' => $embed_data,
                            'position' => 0
                        ]
                    );
                }
            }
        }

        return $embed_commands;
    }
}

<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\EntityInterface;
use Honeybee\Infrastructure\Event\NoOpSignal;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeybee\Model\Task\ModifyAggregateRoot\AddEmbeddedEntity\AddEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\ModifyEmbeddedEntity\ModifyEmbeddedEntityCommand;
use Honeybee\Model\Task\ModifyAggregateRoot\RemoveEmbeddedEntity\RemoveEmbeddedEntityCommand;
use Trellis\Runtime\Attribute\EmbeddedEntityList\EmbeddedEntityListAttribute;
use Trellis\Runtime\Attribute\EntityReferenceList\EntityReferenceListAttribute;
use Trellis\Runtime\Validator\Result\IncidentInterface;
use Workflux\StatefulSubjectInterface;

class ModifyAggregateRootStateNode extends AggregateRootCommandStateNode
{
    public function onExit(StatefulSubjectInterface $process_state)
    {
        parent::onExit($process_state);

        $execution_context = $process_state->getExecutionContext();
        $incoming_event = $execution_context->getParameter('incoming_event');
        if ($incoming_event instanceof NoOpSignal) {
            $command_data = $incoming_event->getCommandData();
            $aggregate_root_identifier = $command_data['aggregate_root_identifier'];
        } else {
            $aggregate_root_identifier = $incoming_event->getAggregateRootIdentifier();
        }

        $export_key = null;
        if ($this->options->has('export_as_reference')) {
            $export_as_reference = $this->options->get('export_as_reference');
            $embed_type = $export_as_reference->get('reference_embed_type');
            $export_key = $export_as_reference->get('export_to');
            $reference_data = [ [ '@type' => $embed_type, 'referenced_identifier' => $aggregate_root_identifier ] ];

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

        return new $command_class([
            'aggregate_root_type' => get_class($aggregate_root_type),
            'aggregate_root_identifier' => $projection->getIdentifier(),
            'known_revision' => $projection->getRevision(),
            'values' => $command_payload,
            'meta_data' => [
                'process_name' => $process_state->getProcessName(),
                'process_uuid' => $process_state->getUuid()
            ],
            'embedded_entity_commands' => $this->buildEmbedCommandList($process_state, $command_payload)
        ]);
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
        $buildCommands = function($attribute, array $cmd_payloads) use ($projection) {
            $commands = [];
            $processed_identifiers = [];
            foreach ($cmd_payloads as $position => $cmd_payload) {
                $referenced_identifier = $cmd_payload['referenced_identifier'];
                $processed_identifiers[] = $referenced_identifier;
                $embedded_entity_list = $projection->getValue($attribute);
                $affected_entity = $embedded_entity_list->filter(
                    function (EntityInterface $reference_embed) use($referenced_identifier) {
                        return $reference_embed->getReferencedIdentifier() === $referenced_identifier;
                    }
                )->getFirst();

                if (!$affected_entity) {
                    $embed_type = $cmd_payload['@type'];
                    unset($cmd_payload['@type']);
                    $commands[] = new AddEmbeddedEntityCommand([
                        'embedded_entity_type' => $embed_type,
                        'parent_attribute_name' => $attribute,
                        'position' => $position,
                        'values' => $cmd_payload
                    ]);
                }
            }

            foreach ($embedded_entity_list as $reference_embed) {
                if (!in_array($reference_embed->getReferencedIdentifier(), $processed_identifiers)) {
                    $commands[] = new RemoveEmbeddedEntityCommand([
                        'embedded_entity_identifier' => $reference_embed->getIdentifier(),
                        'embedded_entity_type' => $reference_embed->getType()->getPrefix(),
                        'parent_attribute_name' => $attribute
                    ]);
                }
            }

            return $commands;
        };

        $reference_commands = [];
        foreach ((array)$this->options->get('link_relations', []) as $attribute_name => $payload_key) {
            if (!is_string($payload_key)) { // dealing with a params instance
                $relation_payload = [];
                foreach ((array)$payload_key as $reference_key) {
                    $relation_payload = array_merge($relation_payload, $payload[$reference_key]);
                }
                $reference_commands = array_merge(
                    $reference_commands,
                    $buildCommands($attribute_name, $relation_payload)
                );
            } elseif (isset($payload[$payload_key])) {
                $reference_commands = array_merge(
                    $reference_commands,
                    $buildCommands($attribute_name, $payload[$payload_key])
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
            function ($attribute) {
                return $attribute instanceof EmbeddedEntityListAttribute
                    && !$attribute instanceof EntityReferenceListAttribute;
            }
        );

        $embed_commands = [];
        foreach ($embed_attributes as $embed_attribute_name => $embed_attribute) {
            if (!array_key_exists($embed_attribute_name, $payload)) {
                continue;
            }

            $add_commands = [];
            $remove_commands = [];
            $modify_commands = [];
            $processed_identifiers = [];
            $embedded_entity_list = $projection->getValue($embed_attribute_name);
            foreach ($payload[$embed_attribute_name] as $pos => $embed_data) {
                $embed_type = $embed_data['@type'];
                unset($embed_data['@type']);
                $affected_entity = $embedded_entity_list->filter(
                    function(EntityInterface $embedded_entity) use ($embed_data) {
                        return isset($embed_data['identifier'])
                            && $embedded_entity->getIdentifier() === $embed_data['identifier'];
                    }
                )->getFirst();

                if (!$affected_entity) {
                    $add_commands[] = new AddEmbeddedEntityCommand([
                        'embedded_entity_type' => $embed_type,
                        'parent_attribute_name' => $embed_attribute_name,
                        'values' => $embed_data,
                        'position' => $pos
                    ]);
                } else {
                    $processed_identifiers[] = $affected_entity->getIdentifier();
                    $modified_data = $this->filterModifiedValues($affected_entity, $embed_data);
                    if (!empty($modified_data)) {
                        // also allow for pos modifications here?
                        $modify_commands[] = new ModifyEmbeddedEntityCommand([
                            'embedded_entity_type' => $embed_type,
                            'parent_attribute_name' => $embed_attribute_name,
                            'embedded_entity_identifier' => $affected_entity->getIdentifier(),
                            'values' => $modified_data
                        ]);
                    }
                }
            }

            foreach ($embedded_entity_list as $embedded_entity) {
                if (!in_array($embedded_entity->getIdentifier(), $processed_identifiers)) {
                    $compensation_cmd = null;
                    foreach ($add_commands as $add_command) {
                        if ($this->isCompensatedBy($embedded_entity, $add_command)) {
                            $compensation_cmd = $add_command;
                            break;
                        }
                    }
                    if ($compensation_cmd) {
                        $cmd_idx = array_search($compensation_cmd, $add_commands, true);
                        array_splice($add_commands, $cmd_idx, 1);
                    } else {
                        $remove_commands[] = new RemoveEmbeddedEntityCommand([
                            'embedded_entity_type' => $embedded_entity->getType()->getPrefix(),
                            'parent_attribute_name' => $embed_attribute_name,
                            'embedded_entity_identifier' => $embedded_entity->getIdentifier()
                        ]);
                    }
                }
            }
            $embed_commands = array_merge($embed_commands, $modify_commands, $remove_commands, $add_commands);
        }

        return $embed_commands;
    }

    protected function isCompensatedBy(EntityInterface $embedded_entity, AddEmbeddedEntityCommand $add_cmd)
    {
        $delta = $this->filterModifiedValues($embedded_entity, $add_cmd->getValues());

        return empty($delta);
    }

    protected function filterModifiedValues(EntityInterface $embedded_entity, array $embed_payload)
    {
        $modified_data = [];
        foreach ($embedded_entity->getType()->getAttributes() as $current_attribute) {
            $attr_name = $current_attribute->getName();
            if (!array_key_exists($attr_name, $embed_payload)) {
                continue;
            }
            $value_holder = $current_attribute->createValueHolder();
            $attr_value = $embed_payload[$attr_name];
            $embed_value = $embedded_entity->getValue($attr_name);
            $result = $value_holder->setValue($attr_value, $embedded_entity);
            if ($result->getSeverity() <= IncidentInterface::NOTICE) {
                if (!$value_holder->sameValueAs($embed_value)) {
                    $modified_data[$attr_name] = $value_holder->toNative();
                }
            } else {
                error_log(
                    sprintf(
                        '[%s] Invalid embed-data given for "%s": %s',
                        __METHOD__,
                        $current_attribute->getPath(),
                        var_export($attr_value, true)
                    )
                );
            }
        }

        return $modified_data;
    }

    protected function getProjection(ProcessStateInterface $process_state)
    {
        return $process_state->getExecutionContext()->getParameter($this->options->get('projection_key'));
    }
}

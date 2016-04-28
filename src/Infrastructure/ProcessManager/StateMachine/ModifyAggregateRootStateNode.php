<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Infrastructure\Event\NoOpSignal;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Workflux\StatefulSubjectInterface;
use Shrink0r\Monatic\Success;

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

        $result = (new AggregateRootCommandBuilder($aggregate_root_type, $command_class))
            ->withProjection($projection)
            ->withValues($command_payload)
            ->withMetadata([
                'process_name' => $process_state->getProcessName(),
                'process_uuid' => $process_state->getUuid()
            ])
            ->build();

        if ($result instanceof Success) {
            return $result->get();
        } else {
            throw new RuntimeError(
                sprintf(
                    '[%s] Process "%s" failed to create aggregate root type "%s" with errors: %s',
                    __METHOD__,
                    $process_state->getProcessName(),
                    $aggregate_root_type->getPrefix(),
                    var_export($result->get(), true)
                )
            );
        }
    }

    protected function getProjection(ProcessStateInterface $process_state)
    {
        return $process_state->getExecutionContext()->getParameter($this->options->get('projection_key'));
    }
}

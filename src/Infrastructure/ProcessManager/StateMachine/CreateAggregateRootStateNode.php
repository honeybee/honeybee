<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Workflux\StatefulSubjectInterface;
use Shrink0r\Monatic\Success;

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
        $aggregate_root_type = $this->getAggregateRootType();
        $payload = $this->getCommandPayload($process_state);
        $command_class = $this->getCommandImplementor($process_state);

        $result = (new AggregateRootCommandBuilder($aggregate_root_type, $command_class))
            ->withValues($payload)
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
                    '[%s] Process "%s" failed to create command for type "%s" with errors: %s',
                    __METHOD__,
                    $process_state->getProcessName(),
                    $aggregate_root_type->getPrefix(),
                    var_export($result->get(), true)
                )
            );
        }
    }
}

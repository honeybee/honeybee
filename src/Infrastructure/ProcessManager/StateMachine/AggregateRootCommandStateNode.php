<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use JmesPath\AstRuntime;
use Workflux\State\State;
use Workflux\StatefulSubjectInterface;

abstract class AggregateRootCommandStateNode extends State
{
    protected $aggregate_root_type_map;

    abstract protected function createCommand(ProcessStateInterface $process_state);

    // @codingStandardsIgnoreStart
    public function __construct(
        $name,
        $type = self::TYPE_ACTIVE,
        array $options = [],
        AggregateRootTypeMap $aggregate_root_type_map
    ) {
        // @codingStandardsIgnoreEnd
        parent::__construct($name, $type, $options);

        $this->aggregate_root_type_map = $aggregate_root_type_map;

        $this->needs('aggregate_root_type')->needs('command');
    }

    public function onEntry(StatefulSubjectInterface $process_state)
    {
        if (!$process_state instanceof ProcessStateInterface) {
            throw new RuntimeError('Only ' . ProcessStateInterface::CLASS . ' subjects supported by ' . static::CLASS);
        }

        parent::onEntry($process_state);

        $command = $this->createCommand($process_state);
        $execution_context = $process_state->getExecutionContext();
        $execution_context->setParameter('command', $command);
    }

    protected function getCommandImplementor()
    {
        $command_class = $this->options->get('command');
        if (!class_exists($command_class)) {
            throw new RuntimeError('Unable to load configured command class: ' . $command_class);
        }

        return $command_class;
    }

    protected function getCommandPayload(ProcessStateInterface $process_state)
    {
        $this->needs('payload_path');

        $payload = $process_state->getPayload();
        $payload_path = $this->options->get('payload_path');
        $jmes_path_runtime = new AstRuntime();
        $command_payload = $jmes_path_runtime($payload_path, $payload);

        $link_relations = $this->options->get('link_relations', []);
        foreach ($link_relations as $attribute_name => $payload_key) {
            if (!is_string($payload_key)) {
                foreach ((array)$payload_key as $attribute_name => $reference_key) {
                    $command_payload[$attribute_name] = $payload[$reference_key];
                }
            } else if (isset($payload[$payload_key])) {
                $command_payload[$attribute_name] = $payload[$payload_key];
            }
        }

        return $command_payload;
    }

    protected function getAggregateRootType()
    {
        $ar_type_prefix = $this->options->get('aggregate_root_type');
        if (!$this->aggregate_root_type_map->hasKey($ar_type_prefix)) {
            throw new RuntimeError('Unable to resolve given ar-prefix: ' . $ar_type_prefix);
        }

        return $this->aggregate_root_type_map->getItem($ar_type_prefix);
    }

    protected function needs($option_key)
    {
        if (!$this->options->has($option_key)) {
            throw new RuntimeError(sprintf('Missing required option "%s"', $option_key));
        }

        return $this;
    }

    protected function requiresVariable($variable_name, ProcessStateInterface $process_state)
    {
        $execution_context = $process_state->getExecutionContext();
        if (!$execution_context->hasParameter($variable_name)) {
            throw new RuntimeError(sprintf('Missing required execution-context variable "%s"', $variable_name));
        }

        return $this;
    }
}

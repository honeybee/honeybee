<?php

namespace Honeybee\Infrastructure\ProcessManager\StateMachine;

use Honeybee\Common\Error\RuntimeError;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\ProcessManager\ProcessStateInterface;
use Honeybee\Projection\ProjectionTypeMap;
use JmesPath\AstRuntime;
use Workflux\Guard\VariableGuard;
use Workflux\StatefulSubjectInterface;

class ProjectionExistsGuard extends VariableGuard
{
    protected $data_access_service;

    protected $projection_type_map;

    // @codingStandardsIgnoreStart
    public function __construct(
        array $options = [],
        DataAccessServiceInterface $data_access_service,
        ProjectionTypeMap $projection_type_map
    ) {
        // @codingStandardsIgnoreEnd
        parent::__construct($options);

        $this->data_access_service = $data_access_service;
        $this->projection_type_map = $projection_type_map;

        $this->needs('projection_type')->needs('identifier_payload_path');
    }

    public function accept(StatefulSubjectInterface $process_state)
    {
        if ($this->hasOption('expression') && !parent::accept($process_state)) {
            return false;
        }

        $projection = $this->findProjection($this->getPayloadIdentifier($process_state));
        if ($projection) {
            $execution_context = $process_state->getExecutionContext();
            if ($export_key = $this->options->get('export_to', false)) {
                $execution_context->setParameter($export_key, $projection);
            }
            if ($this->options->has('export_as_reference')) {
                $export_config = $this->options->get('export_as_reference');
                $embed_type = $export_config->get('reference_embed_type');
                $execution_context->setParameter(
                    $export_config->get('export_to'),
                    [ [ '@type' => $embed_type, 'referenced_identifier' => $projection->getIdentifier() ] ]
                );
            }
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return static::CLASS;
    }

    protected function findProjection($identifier)
    {
        $query_service = $this->data_access_service->getProjectionQueryServiceByType($this->getProjectionType());
        if ($query_attribute_path = $this->options->get('query_attribute_path', false)) {
            $result = $query_service->find($this->buildQuery($query_attribute_path, $identifier));
        } else {
            $result = $query_service->findByIdentifier($identifier);
        }

        $projection = null;
        if ($result->getTotalCount() > 0) {
            $projection = $result->getFirstResult();
            if ($result->getTotalCount() > 1) {
                // @todo log multiple matches for same oen-id, shouldn't happen.
            }
        }

        return $projection;
    }

    protected function getPayloadIdentifier(ProcessStateInterface $process_state)
    {
        $identifier_path = $this->options->get('identifier_payload_path', false);
        $jmes_path_runtime = new AstRuntime();
        $identifier = $jmes_path_runtime($identifier_path, $process_state->getPayload());
        if (!$identifier) {
            throw new RuntimeError(
                'Unable to resolve required "identifier" from payload-path:' .
                $identifier_path . ' within ' . $process_state->getExecutionContext()->getStateMachineName()
            );
        }

        return $identifier;
    }

    protected function buildQuery($attribute_path, $identifier)
    {
        return new Query(
            new CriteriaList,
            new CriteriaList([ new AttributeCriteria($attribute_path, $identifier) ]),
            new CriteriaList,
            0,
            1
        );
    }

    protected function getProjectionType()
    {
        $projection_prefix = $this->options->get('projection_type');
        if (!$this->projection_type_map->hasKey($projection_prefix)) {
            throw new RuntimeError('Unable to resolve given projection-prefix: ' . $projection_prefix);
        }

        return $this->projection_type_map->getItem($projection_prefix);
    }

    protected function needs($option_key)
    {
        if (!$this->options->has($option_key)) {
            throw new RuntimeError(sprintf('Missing require option "%s"', $option_key));
        }

        return $this;
    }
}

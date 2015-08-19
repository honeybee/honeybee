<?php

namespace Honeybee\Infrastructure\Saga;

use Honeybee\Common\Error\RuntimeError;
use Rhumsaa\Uuid\Uuid as UuidGenerator;
use Trellis\Common\Object;
use Workflux\ExecutionContext;
use Workflux\StatefulSubjectInterface;

class SagaSubject extends Object implements SagaSubjectInterface, StatefulSubjectInterface
{
    /**
     * @hiddenProperty
     */
    protected $execution_context;

    protected $uuid;

    protected $payload;

    protected $state_name;

    protected $saga_name;

    public function __construct(array $object_state)
    {
        parent::__construct($object_state);

        if (!$this->uuid) {
            $this->uuid = UuidGenerator::uuid4()->toString();
        }

        if (empty($this->payload)) {
            throw new RuntimeError('Missing required payload.');
        }
        if (empty($this->saga_name)) {
            throw new RuntimeError('Missing required saga_name.');
        }
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getSagaName()
    {
        return $this->saga_name;
    }

    public function getStateName()
    {
        return $this->getExecutionContext()->getCurrentStateName();
    }

    public function getExecutionContext()
    {
        if (!$this->execution_context) {
            $this->execution_context = new ExecutionContext(
                $this->saga_name,
                $this->state_name
            );
        }

        return $this->execution_context;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['state_name'] = $this->getStateName();

        return $array;
    }

    protected function setImportData(array $payload)
    {
        $this->payload = $payload;
    }
}

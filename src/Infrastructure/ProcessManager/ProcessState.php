<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Common\Error\RuntimeError;
use Ramsey\Uuid\Uuid as UuidGenerator;
use Trellis\Common\Object;
use Workflux\ExecutionContext;
use Workflux\StatefulSubjectInterface;

class ProcessState extends Object implements ProcessStateInterface, StatefulSubjectInterface
{
    /**
     * @hiddenProperty
     */
    protected $execution_context;

    protected $uuid;

    protected $payload;

    protected $state_name;

    protected $process_name;

    public function __construct(array $object_state)
    {
        parent::__construct($object_state);

        if (!$this->uuid) {
            $this->uuid = UuidGenerator::uuid4()->toString();
        }

        if (empty($this->payload)) {
            throw new RuntimeError('Missing required payload.');
        }
        if (empty($this->process_name)) {
            throw new RuntimeError('Missing required process_name.');
        }
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getProcessName()
    {
        return $this->process_name;
    }

    public function getPayload()
    {
        $execution_context = $this->getExecutionContext();

        return $execution_context->getParameters()->toArray();
    }

    public function getStateName()
    {
        return $this->getExecutionContext()->getCurrentStateName();
    }

    public function getExecutionContext()
    {
        if (!$this->execution_context) {
            $this->execution_context = new ExecutionContext($this->process_name, $this->state_name, $this->payload);
        }

        return $this->execution_context;
    }

    public function toArray()
    {
        $process_state_as_array = parent::toArray();

        $process_state_as_array['state_name'] = $this->getStateName();
        $process_state_as_array['payload'] = $this->getPayload();

        return $process_state_as_array;
    }

    protected function setPayload(array $payload)
    {
        $this->payload = $payload;
    }
}

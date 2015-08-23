<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Honeybee\Common\Error\RuntimeError;
use Rhumsaa\Uuid\Uuid as UuidGenerator;
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

    public function getPayload()
    {
        $context = $this->getExecutionContext();
        if ($context->hasParameter('payload')) {
            return $context->getParameter('payload')->toArray();
        }
        return [];
    }

    public function getProcessName()
    {
        return $this->process_name;
    }

    public function getStateName()
    {
        return $this->getExecutionContext()->getCurrentStateName();
    }

    public function getExecutionContext()
    {
        if (!$this->execution_context) {
            $this->execution_context = new ExecutionContext(
                $this->process_name,
                $this->state_name,
                [ 'payload' => $this->payload ]
            );
        }

        return $this->execution_context;
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['state_name'] = $this->getStateName();
        $array['payload'] = $this->getPayload();

        return $array;
    }

    protected function setImportData(array $payload)
    {
        $this->payload = $payload;
    }
}

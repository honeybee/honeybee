<?php

namespace Honeybee\Infrastructure\Job;

use Ramsey\Uuid\Uuid;

abstract class Job implements JobInterface
{
    protected $uuid;

    protected $metadata;

    public function __construct(array $state = [])
    {
        $this->metadata = [];

        foreach ($state as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }

        if (!$this->uuid) {
            $this->uuid = Uuid::uuid4()->toString();
        }
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }
}

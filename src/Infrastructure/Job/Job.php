<?php

namespace Honeybee\Infrastructure\Job;

use Ramsey\Uuid\Uuid;
use Trellis\Common\Object;

abstract class Job extends Object implements JobInterface
{
    protected $uuid;

    public function __construct(array $state = [])
    {
        parent::__construct($state);

        if (!$this->uuid) {
            $this->uuid = Uuid::uuid4()->toString();
        }
    }

    public function getUuid()
    {
        return $this->uuid;
    }
}

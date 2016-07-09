<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Trellis\Collection\TypedMap;
use Trellis\Collection\UniqueItemInterface;

class ProcessMap extends TypedMap implements UniqueItemInterface
{
    public function __construct(array $processes = [])
    {
        parent::__construct(ProcessInterface::CLASS, $processes);
    }
}

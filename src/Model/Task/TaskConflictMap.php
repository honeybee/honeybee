<?php

namespace Honeybee\Model\Task;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedMap;

class TaskConflictMap extends TypedMap implements UniqueValueInterface
{
    protected function getItemImplementor()
    {
        return TaskConflictInterface::CLASS;
    }
}

<?php

namespace Honeybee\Model\Task;

use Trellis\Common\Collection\UniqueCollectionInterface;
use Trellis\Common\Collection\TypedMap;

class TaskConflictMap extends TypedMap implements UniqueCollectionInterface
{
    protected function getItemImplementor()
    {
        return TaskConflictInterface::CLASS;
    }
}

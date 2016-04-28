<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Trellis\Common\Collection\TypedMap;
use Trellis\Common\Collection\UniqueValueInterface;
use Honeybee\Common\Error\RuntimeError;

class ProcessMap extends TypedMap implements UniqueValueInterface
{
    public function getByName($process_name)
    {
        if (!$this->hasKey($process_name)) {
            throw new RuntimeError('Unable to find state-machine for name: ' . $process_name);
        }

        return $this->getItem($process_name);
    }

    protected function getItemImplementor()
    {
        return ProcessInterface::CLASS;
    }
}

<?php

namespace Honeybee\Infrastructure\ProcessManager;

use Trellis\Common\Collection\TypedMap;
use Honeybee\Common\Error\RuntimeError;
use Trellis\Common\Collection\UniqueKeyInterface;
use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;

class ProcessMap extends TypedMap implements UniqueKeyInterface, UniqueValueInterface, MandatoryKeyInterface
{
    protected function getItemImplementor()
    {
        return ProcessInterface::CLASS;
    }
}

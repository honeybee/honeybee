<?php

namespace Honeybee\Infrastructure\Workflow;

use Honeybee\Infrastructure\Config\ConfigInterface;
use Trellis\Common\Collection\TypedMap;

class StateMachineConfigMap extends TypedMap
{
    protected function getItemImplementor()
    {
        return ConfigInterface::CLASS;
    }
}

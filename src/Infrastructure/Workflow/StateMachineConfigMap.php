<?php

namespace Honeybee\Infrastructure\Workflow;

use Trellis\Common\Collection\TypedMap;
use Honeybee\Infrastructure\Config\ConfigInterface;

class StateMachineConfigMap extends TypedMap
{
    protected function getItemImplementor()
    {
        return ConfigInterface::CLASS;
    }
}

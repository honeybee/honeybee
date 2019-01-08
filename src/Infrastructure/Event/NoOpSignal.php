<?php

namespace Honeybee\Infrastructure\Event;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;

class NoOpSignal extends Event
{
    protected $command_data;

    public function getCommandData()
    {
        return $this->command_data;
    }

    public function getType()
    {
        $command_type = $this->command_data['@type'];
        if (!class_exists($command_type)) {
            throw new RuntimeError('Unable to load command class: ' . $command_type);
        }

        return call_user_func([ $command_type, 'getType' ]) . '.noop';
    }

    protected function setCommandData(array $command_data)
    {
        $this->command_data = $command_data;
    }

    protected function guardRequiredState()
    {
        Assertion::isArray($this->command_data);
    }
}

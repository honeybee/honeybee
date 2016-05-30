<?php

namespace Honeybee\Projection;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Event\Event;

class ProjectionUpdatedEvent extends Event
{
    protected $projection_identifier;

    protected $projection_type;

    protected $data;

    public function getProjectionIdentifier()
    {
        return $this->projection_identifier;
    }

    public function getProjectionType()
    {
        return $this->projection_type;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getType()
    {
        $fqcn_parts = explode('\\', $this->projection_type);
        if (count($fqcn_parts) < 3) {
            throw new RuntimeError(
                sprintf(
                    'A concrete projection class must be made up of at least three namespace parts: ' .
                    '(vendor, package, type, event), in order to support auto-type generation.' .
                    ' The given class %s only has %d parts.',
                    $this->projection_type,
                    count($fqcn_parts)
                )
            );
        }
        $vendor = strtolower(array_shift($fqcn_parts));
        $package = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $type = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $event_parts = explode('\\', static::CLASS);
        $event = str_replace('_event', '', StringToolkit::asSnakeCase(array_pop($event_parts)));

        return sprintf('%s.%s.%s.%s', $vendor, $package, $type, $event);
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::string($this->projection_type);
        Assertion::isArray($this->data);
        Assertion::regex(
            $this->projection_identifier,
            '/[\w\.\-_]{1,128}\-\w{8}\-\w{4}\-\w{4}\-\w{4}\-\w{12}\-\w{2}_\w{2}\-\d+/'
        );
    }
}

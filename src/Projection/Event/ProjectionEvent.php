<?php

namespace Honeybee\Projection\Event;

use Assert\Assertion;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Event\Event;

abstract class ProjectionEvent extends Event implements ProjectionEventInterface
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
        $event_parts = explode('\\', static::CLASS);
        $event = str_replace('_event', '', StringToolkit::asSnakeCase(array_pop($event_parts)));
        return sprintf('%s.%s', $this->projection_type, $event);
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::regex(
            $this->projection_type,
            // @todo improve regex to match double underscores/hyphens
            '#^([a-z][a-z_-]+(?<![_-])\.){2}[a-z][a-z_-]+(?<![_-])$#'
        );
        Assertion::isArray($this->data);
        Assertion::regex(
            $this->projection_identifier,
            '/[\w\.\-_]{1,128}\-\w{8}\-\w{4}\-\w{4}\-\w{4}\-\w{12}\-\w{2}_\w{2}\-\d+/'
        );
    }
}

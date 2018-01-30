<?php

namespace Honeybee\Model\Event;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Trellis\Common\BaseObject;

abstract class EmbeddedEntityEvent extends BaseObject implements
    EmbeddedEntityEventInterface,
    HasEmbeddedEntityEventsInterface
{
    protected $data = [];

    protected $embedded_entity_identifier;

    protected $embedded_entity_type;

    protected $parent_attribute_name;

    protected $embedded_entity_events;

    public function __construct(array $state = [])
    {
        $this->embedded_entity_events = new EmbeddedEntityEventList;

        parent::__construct($state);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getEmbeddedEntityIdentifier()
    {
        return $this->embedded_entity_identifier;
    }

    public function getEmbeddedEntityType()
    {
        return $this->embedded_entity_type;
    }

    public function getParentAttributeName()
    {
        return $this->parent_attribute_name;
    }

    public function getEmbeddedEntityEvents()
    {
        return $this->embedded_entity_events;
    }

    protected function setEmbeddedEntityEvents($embedded_entity_events)
    {
        if ($embedded_entity_events instanceof EmbeddedEntityEventList) {
            $this->embedded_entity_events = $embedded_entity_events;
        } elseif (is_array($embedded_entity_events)) {
            $this->embedded_entity_events = new EmbeddedEntityEventList;
            foreach ($embedded_entity_events as $embedded_entity_event) {
                $event_class = $embedded_entity_event[self::OBJECT_TYPE];
                $this->embedded_entity_events->push(new $event_class($embedded_entity_event));
            }
        } else {
            throw new RuntimeError('Invalid type given as embedded_entity_events property value.');
        }
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::string($this->parent_attribute_name);
        Assertion::string($this->embedded_entity_type);
        Assertion::uuid($this->embedded_entity_identifier);
        Assertion::isArray($this->embedded_entity_events);
        Assertion::isArray($this->data);
    }
}

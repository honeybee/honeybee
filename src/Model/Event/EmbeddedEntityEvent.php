<?php

namespace Honeybee\Model\Event;

use Trellis\Common\Object;

abstract class EmbeddedEntityEvent
    extends Object
    implements EmbeddedEntityEventInterface, HasEmbeddedEntityEventsInterface
{
    protected $data = [];

    protected $embedded_entity_identifier;

    protected $embedded_entity_type;

    protected $parent_attribute_name;

    protected $embedded_entity_events;

    public function __construct(array $state = [])
    {
        $this->embedded_entity_events = new EmbeddedEntityEventList();

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
        } else if (is_array($embedded_entity_events)) {
            $this->embedded_entity_events = new EmbeddedEntityEventList();
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

        assert($this->embedded_entity_type !== null, 'embedded-entity-type is set');
        assert($this->embedded_entity_identifier !== null, 'embedded-entity-identifier is set');
        assert(is_array($this->data), 'data is an array');
    }
}

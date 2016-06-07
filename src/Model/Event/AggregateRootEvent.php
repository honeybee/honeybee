<?php

namespace Honeybee\Model\Event;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Event\Event;

abstract class AggregateRootEvent extends Event implements AggregateRootEventInterface, HasEmbeddedEntityEventsInterface
{
    protected $aggregate_root_identifier;

    protected $aggregate_root_type;

    protected $embedded_entity_events;

    protected $seq_number;

    public function __construct(array $state = [])
    {
        $this->embedded_entity_events = new EmbeddedEntityEventList;

        parent::__construct($state);
    }

    public function getSeqNumber()
    {
        return $this->seq_number;
    }

    public function getAggregateRootIdentifier()
    {
        return $this->aggregate_root_identifier;
    }

    public function getAggregateRootType()
    {
        return $this->aggregate_root_type;
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
            foreach ($embedded_entity_events as $embedded_event_data) {
                $event_class = $embedded_event_data[self::OBJECT_TYPE];
                $this->embedded_entity_events->push(new $event_class($embedded_event_data));
            }
        } else {
            throw new RuntimeError('Invalid type given as embedded_entity_events property value.');
        }
    }

    public function getAffectedAttributeNames()
    {
        $affected_attributes = array_keys($this->getData());
        $embedded_entity_events = $this->getEmbeddedEntityEvents();
        foreach ($embedded_entity_events as $embedded_entity_event) {
            $affected_attributes[] = $embedded_entity_event->getParentAttributeName();
        }

        return $affected_attributes;
    }

    public function getType()
    {
        $fqcn_parts = explode('\\', static::CLASS);
        if (count($fqcn_parts) < 4) {
            throw new RuntimeError(
                sprintf(
                    'A concrete event class must be made up of at least four namespace parts: ' .
                    '(vendor, package, type, event), in order to support auto-type generation.' .
                    ' The given class %s only has %d parts.',
                    static::CLASS,
                    count($fqcn_parts)
                )
            );
        }
        $vendor = strtolower(array_shift($fqcn_parts));
        $package = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $type = StringToolkit::asSnakeCase(array_shift($fqcn_parts));
        $event = str_replace('_event', '', StringToolkit::asSnakeCase(array_pop($fqcn_parts)));

        return sprintf('%s.%s.%s.%s', $vendor, $package, $type, $event);
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::regex(
            $this->aggregate_root_type,
            // @todo improve regex to match double underscores/hyphens
            '#^([a-z][a-z_-]+(?<![_-])\.){2}[a-z][a-z_-]+(?<![_-])$#'
        );
        Assertion::integer($this->seq_number);
        Assertion::isInstanceOf($this->embedded_entity_events, EmbeddedEntityEventList::CLASS);
        Assertion::regex(
            $this->aggregate_root_identifier,
            '/[\w\.\-_]{1,128}\-\w{8}\-\w{4}\-\w{4}\-\w{4}\-\w{12}\-\w{2}_\w{2}\-\d+/'
        );
    }
}

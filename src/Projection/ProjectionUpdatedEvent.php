<?php

namespace Honeybee\Projection;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Infrastructure\Event\Event;
use Honeybee\Model\Event\AggregateRootEventInterface;

class ProjectionUpdatedEvent extends Event
{
    protected $source_event_data;

    protected $projection_type;

    protected $projection_data;

    public function getSourceEventData()
    {
        return $this->source_event_data;
    }

    public function getProjectionType()
    {
        return $this->projection_type;
    }

    public function getProjectionData()
    {
        return $this->projection_data;
    }

    public function getType()
    {
        return sprintf('%s.resource_updated', $this->getProjectionType()->getPrefix());
    }

    protected function setSourceEventData(array $source_event_data)
    {
        $this->source_event_data = $source_event_data;
    }

    protected function setProjectionData(array $projection_data)
    {
        $this->projection_data = $projection_data;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->getSourceEventData() !== null, 'source_event_data is set');
        assert($this->getProjectionType() !== null, 'projection_type is set');
        assert($this->getProjectionData() !== null, 'projection_data is set');
    }
}

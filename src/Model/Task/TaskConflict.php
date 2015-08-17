<?php

namespace Honeybee\Model\Task;

use Honeybee\Projection\ProjectionInterface;
use Trellis\Common\Object;

class TaskConflict extends Object implements TaskConflictInterface
{
    /**
     * @var AggregateRootEventList $conflicting_events
     */
    protected $conflicting_events;

    /**
     * @var ProjectionInterface $current_resource
     */
    protected $current_resource;

    /**
     * @var ProjectionInterface $conflicted_resource
     */
    protected $conflicted_resource;

    /**
     * @var array $conflicting_attribute_names
     */
    protected $conflicting_attribute_names;

    /**
     * Return a list of events that are involved in the conflict.
     *
     * @return AggregateRootEventList
     */
    public function getConflictingEvents()
    {
        return $this->conflicting_events;
    }

    /**
     * Return the current HEAD revision of the affected resource.
     *
     * @return ProjectionInterface
     */
    public function getCurrentResource()
    {
        return $this->current_resource;
    }

    /**
     * Return the CONFLICT revision of the affected resource.
     *
     * @return ProjectionInterface
     */
    public function getConflictedResource()
    {
        return $this->conflicted_resource;
    }

    /**
     * Return an array of attribute names that are affected by the conflict.
     *
     * @return array
     */
    public function getConflictingAttributeNames()
    {
        return $this->conflicting_attribute_names;
    }
}

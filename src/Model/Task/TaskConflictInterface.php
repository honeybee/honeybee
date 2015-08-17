<?php

namespace Honeybee\Model\Task;

interface TaskConflictInterface
{
    /**
     * @return AggregateRootEventList
     */
    public function getConflictingEvents();

    /**
     * @return ProjectionInterface
     */
    public function getCurrentResource();

    /**
     * @return ProjectionInterface
     */
    public function getConflictedResource();

    /**
     * @return array
     */
    public function getConflictingAttributeNames();
}

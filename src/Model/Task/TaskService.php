<?php

namespace Honeybee\Model\Task;

use Honeybee\Model\Task\TaskConflictMap;
use Trellis\Common\Object;

class TaskService extends Object implements TaskServiceInterface
{
    protected $task_conflict_map;

    protected $last_task_conflict;

    public function __construct()
    {
        $this->task_conflict_map = new TaskConflictMap();
    }

    public function addTaskConflict(TaskConflictInterface $task_conflict)
    {
        $identifier = $task_conflict->getCurrentResource()->getIdentifier();
        $this->task_conflict_map->setItem($identifier, $task_conflict);
        $this->last_task_conflict = $task_conflict;
    }

    public function removeTaskConflict(TaskConflictInterface $task_conflict)
    {
        $this->task_conflict_map->removeItem($task_conflict);
    }

    public function getTaskConflicts()
    {
        return $this->task_conflict_map;
    }

    public function hasTaskConflicts()
    {
        return !$this->task_conflict_map->isEmpty();
    }

    public function getLastTaskConflict()
    {
        return $this->last_task_conflict;
    }
}

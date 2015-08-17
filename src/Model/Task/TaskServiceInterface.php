<?php

namespace Honeybee\Model\Task;

interface TaskServiceInterface
{
    public function addTaskConflict(TaskConflictInterface $task_conflict);

    public function removeTaskConflict(TaskConflictInterface $task_conflict);

    public function getTaskConflicts();

    public function hasTaskConflicts();

    public function getLastTaskConflict();
}

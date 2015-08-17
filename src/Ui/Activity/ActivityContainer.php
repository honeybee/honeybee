<?php

namespace Honeybee\Ui\Activity;

use Trellis\Common\Object;
use Honeybee\Common\Error\RuntimeError;

class ActivityContainer extends Object implements ActivityContainerInterface
{
    protected $scope;

    protected $activity_map;

    public function getScope()
    {
        return $this->scope;
    }

    public function getActivityMap()
    {
        return $this->activity_map;
    }

    public function getActivityByName($name)
    {
        if (!$this->activity_map->hasKey($name)) {
            throw new RuntimeError(
                sprintf(
                    'Activity of name "%s" not found in container with scope "%s".',
                    $name,
                    $this->scope
                )
            );
        }

        return $this->activity_map->getItem($name);
    }

    protected function setActivityMap(ActivityMap $activity_map)
    {
        $this->activity_map = $activity_map;
    }
}

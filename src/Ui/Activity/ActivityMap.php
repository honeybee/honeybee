<?php

namespace Honeybee\Ui\Activity;

use Trellis\Common\Collection\UniqueValueInterface;
use Trellis\Common\Collection\TypedMap;

class ActivityMap extends TypedMap implements UniqueValueInterface
{
    public function filterByUrlParameter($name, $value)
    {
        return $this->filter(
            function ($activity) use ($name, $value) {
                return $activity->getUrl()->getParameter($name) === $value;
            }
        );
    }

    public function filterByType($type)
    {
        return $this->filter(
            function ($activity) use ($type) {
                return $activity->getType() === $type;
            }
        );
    }

    protected function getItemImplementor()
    {
        return Activity::CLASS;
    }
}

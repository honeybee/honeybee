<?php

namespace Honeybee\Projection\Event;

use Honeybee\Infrastructure\Event\EventInterface;

interface ProjectionEventInterface extends EventInterface
{
    public function getProjectionIdentifier();

    public function getProjectionType();

    public function getData();
}

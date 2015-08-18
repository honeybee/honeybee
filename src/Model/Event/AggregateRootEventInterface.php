<?php

namespace Honeybee\Model\Event;

use Honeybee\Infrastructure\Event\EventInterface;

interface AggregateRootEventInterface extends EventInterface
{
    public function getAggregateRootIdentifier();

    public function getAggregateRootType();

    public function getData();

    public function getEmbeddedEntityEvents();

    public function getAffectedAttributeNames();

    public function getSeqNumber();

}

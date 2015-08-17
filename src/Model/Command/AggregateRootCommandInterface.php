<?php

namespace Honeybee\Model\Command;

use Honeybee\Model\Event\AggregateRootEventInterface;

interface AggregateRootCommandInterface extends AggregateRootTypeCommandInterface
{
    public function getAggregateRootIdentifier();

    public function getKnownRevision();

    public function getAffectedAttributeNames();

    public function conflictsWith(AggregateRootEventInterface $event, array &$conflicting_changes = []);
}

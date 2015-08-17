<?php

namespace Honeybee\Model\Command;

use Honeybee\Model\Event\EmbeddedEntityEventInterface;

interface EmbeddedEntityCommandInterface extends EmbeddedEntityTypeCommandInterface
{
    public function getEmbeddedEntityIdentifier();

    public function getAffectedAttributeNames();

    public function conflictsWith(EmbeddedEntityEventInterface $event, array &$conflicting_changes = []);
}

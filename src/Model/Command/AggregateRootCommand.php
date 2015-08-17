<?php

namespace Honeybee\Model\Command;

use Honeybee\Model\Task\ModifyAggregateRoot\AggregateRootModifiedEvent;
use Honeybee\Model\Event\AggregateRootEventInterface;

abstract class AggregateRootCommand extends AggregateRootTypeCommand implements AggregateRootCommandInterface
{
    protected $aggregate_root_identifier;

    protected $known_revision;

    public function getAggregateRootIdentifier()
    {
        return $this->aggregate_root_identifier;
    }

    public function getKnownRevision()
    {
        return $this->known_revision;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        assert($this->getAggregateRootIdentifier() !== null, '"aggregate_root_identifier" is set');
        assert($this->getKnownRevision() !== null, '"known_revision" is set');
    }
}

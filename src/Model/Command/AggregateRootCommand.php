<?php

namespace Honeybee\Model\Command;

use Assert\Assertion;

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

        Assertion::integer($this->known_revision);
        Assertion::regex(
            $this->aggregate_root_identifier,
            '/[\w\.\-_]{1,128}\-\w{8}\-\w{4}\-\w{4}\-\w{4}\-\w{12}\-\w{2}_\w{2}\-\d+/'
        );
    }

    public function __toString()
    {
        return static::CLASS.' for '.$this->aggregate_root_identifier.' with known_revision '.$this->known_revision;
    }
}

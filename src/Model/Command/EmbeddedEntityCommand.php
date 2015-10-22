<?php

namespace Honeybee\Model\Command;

use Assert\Assertion;

abstract class EmbeddedEntityCommand extends EmbeddedEntityTypeCommand implements EmbeddedEntityCommandInterface
{
    protected $embedded_entity_identifier;

    public function getEmbeddedEntityIdentifier()
    {
        return $this->embedded_entity_identifier;
    }

    protected function guardRequiredState()
    {
        parent::guardRequiredState();

        Assertion::uuid($this->embedded_entity_identifier);
    }
}

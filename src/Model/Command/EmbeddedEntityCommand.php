<?php

namespace Honeybee\Model\Command;

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

        assert($this->embedded_entity_identifier !== null, '"embedded_entity_identifier" is set');
    }
}

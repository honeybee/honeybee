<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Entity\ReferenceInterface;

abstract class ReferencedEntity extends EmbeddedEntity implements ReferenceInterface
{
    public function getReferencedIdentifier()
    {
        return $this->getValue('referenced_identifier');
    }
}

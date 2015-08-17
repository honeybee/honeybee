<?php

namespace Honeybee\Model\Aggregate;

use Trellis\Runtime\Entity\EntityReferenceInterface;

abstract class ReferencedEntity extends EmbeddedEntity implements EntityReferenceInterface
{
    public function getReferencedIdentifier()
    {
        return $this->getValue('referenced_identifier');
    }
}

<?php

namespace Honeybee\Projection;

use Trellis\Runtime\Entity\EntityReferenceInterface;

abstract class ReferencedEntity extends EmbeddedEntity implements EntityReferenceInterface
{
    public function getReferencedIdentifier()
    {
        return $this->getValue('referenced_identifier');
    }

    /**
     * Returns the referenced entity's modified date.
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->getValue('modified_at');
    }

    /**
     * Returns the referenced entity's workflow state name.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->getValue('workflow_state');
    }
}

<?php

namespace Honeybee\Projection;

use Trellis\Entity\ReferenceInterface;

abstract class ReferencedEntity extends EmbeddedEntity implements ReferenceInterface
{
    public function getReferencedIdentifier()
    {
        return $this->get('referenced_identifier');
    }

    /**
     * Returns the referenced entity's modified date.
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->get('modified_at');
    }

    /**
     * Returns the referenced entity's workflow state name.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->get('workflow_state');
    }
}

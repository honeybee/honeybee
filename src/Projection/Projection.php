<?php

namespace Honeybee\Projection;

use Assert\Assertion;
use Honeybee\Common\Error\RuntimeError;

abstract class Projection extends Entity implements ProjectionInterface
{
    /**
     * Return a projection uuid.
     *
     * @return \Trellis\EntityType\Attribute\Uuid\Uuid
     */
    public function getUuid()
    {
        return $this->get('uuid');
    }

    /**
     * Returns an projection language.
     *
     * @return \Trellis\EntityType\Attribute\Text\Text
     */
    public function getLanguage()
    {
        return $this->get('language');
    }

    /**
     * Returns an projection current (known)revision.
     *
     * @return \Trellis\EntityType\Attribute\Integer\Integer
     */
    public function getRevision()
    {
        return $this->get('revision');
    }

    /**
     * Returns the projection version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->get('version');
    }

    /**
     * Returns current workflow state name.
     *
     * @return \Trellis\EntityType\Attribute\Text\Text
     */
    public function getWorkflowState()
    {
        return $this->get('workflow_state');
    }

    /**
     * Returns current workflow parameters.
     *
     * @return \Trellis\EntityType\Attribute\KeyValueList\KeyValueList
     */
    public function getWorkflowParameters()
    {
        return $this->get('workflow_parameters');
    }

    /**
     * Returns the projection created date.
     *
     * @return \Trellis\EntityType\Attribute\Timestamp\Timestamp
     */
    public function getCreatedAt()
    {
        return $this->get('created_at');
    }

    /**
     * Returns the projections modified date.
     *
     * @return \Trellis\EntityType\Attribute\Timestamp\Timestamp
     */
    public function getModifiedAt()
    {
        return $this->get('modified_at');
    }

    /**
     * @return \Trellis\EntityType\Attribute\Text\Text
     *
     * @throws RuntimeError
     */
    public function getParentNodeId()
    {
        Assertion::true(
            $this->getEntityType()->isHierarchical(),
            sprintf('"is_hierarchical" option not enabled on type: %s', $this->getEntityType()->getName())
        );
        return $this->get('parent_node_id');
    }

    /**
     * @return \Trellis\EntityType\Attribute\Text\Text
     *
     * @throws RuntimeError
     */
    public function getMaterializedPath()
    {
        Assertion::true(
            $this->getEntityType()->isHierarchical(),
            sprintf('"is_hierarchical" option not enabled on type: %s', $this->getEntityType()->getName())
        );
        return $this->get('materialized_path');
    }
}

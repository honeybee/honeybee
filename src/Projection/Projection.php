<?php

namespace Honeybee\Projection;

use Honeybee\Common\Error\RuntimeError;

abstract class Projection extends Entity implements ProjectionInterface
{
    /**
     * Return a projection uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->get('uuid');
    }

    /**
     * Returns an projection language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->get('language');
    }

    /**
     * Returns an projection current (known)revision.
     *
     * @return string
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
     * Returns an projection slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->get('slug');
    }

    /**
     * Returns current workflow state name.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->get('workflow_state');
    }

    /**
     * Returns current workflow parameters.
     *
     * @return string
     */
    public function getWorkflowParameters()
    {
        return $this->get('workflow_parameters');
    }

    /**
     * Returns the projection created date.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->get('created_at');
    }

    /**
     * Returns the projections modified date.
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->get('modified_at');
    }

    public function getParentNodeId()
    {
        if (!$this->getType()->isHierarchical()) {
            throw new RuntimeError(
                sprintf('"is_hierarchical" option not enabled on type: %s', $this->getType()->getName())
            );
        }
        return $this->get('parent_node_id');
    }

    public function getMaterializedPath()
    {
        if (!$this->getType()->isHierarchical()) {
            throw new RuntimeError(
                sprintf('"is_hierarchical" option not enabled on type: %s', $this->getType()->getName())
            );
        }
        return $this->get('materialized_path');
    }
}

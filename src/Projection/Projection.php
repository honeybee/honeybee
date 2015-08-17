<?php

namespace Honeybee\Projection;

use Honeybee\Entity;
use Honeybee\Common\Error\RuntimeError;
use Honeybee\Common\ScopeKeyInterface;

abstract class Projection extends Entity implements ProjectionInterface
{
    /**
     * Return the resource's short identifier.
     *
     * @return string
     */
    public function getShortIdentifier()
    {
        $type = $this->getType();

        return sprintf('%s-%s', $type->getPrefix(), $this->getShortId());
    }

    /**
     * Return a resource's uuid.
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->getValue('uuid');
    }

    /**
     * Returns an resource's language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->getValue('language');
    }

    /**
     * Returns an resource's current (known)revision.
     *
     * @return string
     */
    public function getRevision()
    {
        return $this->getValue('revision');
    }

    /**
     * Returns an resource's short-id.
     *
     * @return string
     */
    public function getShortId()
    {
        return $this->getValue('short_id');
    }

    /**
     * Returns an resource's slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->getValue('slug');
    }

    /**
     * Returns current workflow state name.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->getValue('workflow_state');
    }

    /**
     * Returns current workflow parameters.
     *
     * @return string
     */
    public function getWorkflowParameters()
    {
        return $this->getValue('workflow_parameters');
    }

    /**
     * Returns the resource's created date.
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getValue('created_at');
    }

    /**
     * Returns the resource's modified date.
     *
     * @return string
     */
    public function getModifiedAt()
    {
        return $this->getValue('modified_at');
    }

    public function getParentNodeId()
    {
        if (!$this->getType()->isHierarchical()) {
            throw new RuntimeError(
                sprintf('"is_hierarchical" option not enabled on type: %s', $this->getType()->getName())
            );
        }
        return $this->getValue('parent_node_id');
    }

    public function getMaterializedPath()
    {
        if (!$this->getType()->isHierarchical()) {
            throw new RuntimeError(
                sprintf('"is_hierarchical" option not enabled on type: %s', $this->getType()->getName())
            );
        }
        return $this->getValue('materialized_path');
    }
}

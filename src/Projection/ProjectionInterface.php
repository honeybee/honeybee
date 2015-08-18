<?php

namespace Honeybee\Projection;

use Honeybee\EntityInterface;

interface ProjectionInterface extends EntityInterface
{
    public function getCreatedAt();

    public function getModifiedAt();

    public function getUuid();

    public function getLanguage();

    public function getVersion();

    public function getRevision();

    public function getWorkflowState();

    public function getWorkflowParameters();

    public function getParentNodeId();

    public function getMaterializedPath();
}

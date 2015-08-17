<?php

namespace Honeybee\Projection;

interface ProjectionInterface
{
    public function getCreatedAt();

    public function getModifiedAt();

    public function getShortIdentifier();

    public function getUuid();

    public function getRevision();

    public function getLanguage();

    public function getShortId();

    public function getWorkflowState();

    public function getWorkflowParameters();
}

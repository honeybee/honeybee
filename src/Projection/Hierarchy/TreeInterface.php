<?php

namespace Honeybee\Projection\Hierarchy;

interface TreeInterface
{
    public function getIdentifier();

    public function getRevision();

    public function setRevision($revision);

    public function getRootNode();

    public function toArray();

    public function getIterator();
}

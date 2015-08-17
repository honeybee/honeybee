<?php

namespace Honeybee\Infrastructure\Domain\Tree;

interface TreeInterface
{
    public function getIdentifier();

    public function getRevision();

    public function setRevision($revision);

    public function getRootNode();

    public function toArray();

    public function getIterator();
}

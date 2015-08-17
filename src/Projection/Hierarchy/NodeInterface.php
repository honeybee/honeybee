<?php

namespace Honeybee\Infrastructure\Domain\Tree;

interface NodeInterface
{
    public function getIdentifier();

    public function getLabel();

    public function getParent();

    public function setParent(NodeInterface $parent);

    public function hasChildren();

    public function getChildren();

    public function addChild(NodeInterface $child);

    public function getChildAt($index);

    public function getFirstChild();

    public function removeChild(NodeInterface $child);

    public function toArray($level = null, $expand = true);
}

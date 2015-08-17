<?php

namespace Honeybee\Infrastructure\Domain\Tree;

abstract class Node implements NodeInterface
{
    protected $parent;

    protected $children;

    protected $depth = 0;

    public function __construct(array $children = array())
    {
        $this->children = array();

        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    public function hasParent()
    {
        return $this->parent instanceof NodeInterface;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(NodeInterface $parent)
    {
        $this->parent = $parent;
        $this->setDepth($parent->getDepth() + 1);
    }

    public function getDepth()
    {
        return $this->depth;
    }

    public function setDepth($depth)
    {
        $this->depth = $depth;

        foreach ($this->getChildren() as $child) {
            $child->setDepth($this->depth + 1);
        }
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getFirstChild()
    {
        return $this->getChildAt(0);
    }

    public function getChildAt($index)
    {
        return isset($this->children[$index]) ? $this->children[$index] : null;
    }

    public function addChild(NodeInterface $child)
    {
        if (!in_array($child, $this->children, true)) {
            $child->setParent($this);
            $this->children[] = $child;
        }
    }

    public function removeChild(NodeInterface $child)
    {
        if (($pos = array_search($child, $this->children))) {
            $child->setParent(null);
            array_splice($this->children, $pos, 1);
        }
    }

    public function toArray($level = null, $expand = true)
    {
        $children = array();

        foreach ($this->getChildren() as $childNode) {
            $children[] = $childNode->toArray($level, $expand);
        }

        $expanded_data = array();
        if (true === $expand) {
            $expanded_data = array(
                'label' => $this->getLabel(),
                'parent' => $this->hasParent() ? $this->getParent()->getIdentifier() : null,
            );
        }

        return array_merge(
            $expanded_data,
            array('identifier' => $this->getIdentifier(), 'children' => $children)
        );
    }

    public function getIterator()
    {
        return new RecursiveNodeIterator($this);
    }
}

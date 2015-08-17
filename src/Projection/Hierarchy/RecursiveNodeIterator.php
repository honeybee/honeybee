<?php

namespace Honeybee\Infrastructure\Domain\Tree;

class RecursiveNodeIterator implements \RecursiveIterator
{
    protected $node;

    protected $children;

    protected $cursor_pos = 0;

    public function __construct(NodeInterface $node)
    {
        $this->node = $node;
        $this->children = $this->node->getChildren();
    }

    public function next()
    {
        $this->cursor_pos++;
    }

    public function rewind()
    {
        $this->cursor_pos = 0;
    }

    public function key()
    {
        return $this->cursor_pos;
    }

    public function valid()
    {
        return isset($this->children[$this->cursor_pos]);
    }

    public function current()
    {
        return $this->children[$this->cursor_pos];
    }

    public function hasChildren()
    {
        return $this->current()->hasChildren();
    }

    public function getChildren()
    {
        return new static($this->current());
    }
}

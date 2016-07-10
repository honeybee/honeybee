<?php

namespace Honeybee\Infrastructure\Domain\Tree;

use Honeybee\Model\Aggregate\AggregateRoot;

class EntityNode extends Node
{
    protected $aggregate_root;

    protected $label_attribute;

    public function __construct(AggregateRoot $aggregate_root, array $children = array())
    {
        $this->aggregate_root = $aggregate_root;

        parent::__construct($children);
    }

    public function getIdentifier()
    {
        return $this->aggregate_root->getIdentifier();
    }

    public function getLabel()
    {
        return $this->aggregate_root->get(
            $this->aggregate_root->getType()->getOption('tree_label_attribute')
        );
    }

    public function getResource()
    {
        return $this->aggregate_roots;
    }
}

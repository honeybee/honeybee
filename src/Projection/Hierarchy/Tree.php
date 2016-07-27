<?php

namespace Honeybee\Projection\Hierarchy;

use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use RecursiveIteratorIterator;

class Tree implements TreeInterface
{
    protected $aggregate_root_type;

    protected $root_node;

    protected $identifier;

    protected $revision;

    public function __construct(AggregateRootTypeInterface $aggregate_root_type, array $data = array())
    {
        $this->aggregate_root_type = $aggregate_root_type;

        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getRevision()
    {
        return $this->revision;
    }

    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    public function getType()
    {
        return $this->aggregate_root_type;
    }

    public function getRootNode()
    {
        return $this->root_node;
    }

    public function getIterator()
    {
        return new RecursiveIteratorIterator(
            $this->getRootNode()->getIterator(),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    public function toArray($level = null, $expand = true)
    {
        return array(
            'root_node' => $this->root_node->toArray($level, $expand),
            'identifier' => $this->getIdentifier(),
            'revision' => $this->getRevision()
        );
    }

    public function hydrate(array $tree_data)
    {
        $service = $this->aggregate_root_type->getService();
        $resources = $service->getMany();

        $resource_id_map = array();
        foreach ($resources['resources'] as $resource) {
            $resource_id_map[$resource->getIdentifier()] = $resource;
        }

        $root_children = array();
        if (isset($tree_data['root_node'])) {
            foreach ($tree_data['root_node']['children'] as $top_level_node) {
                if (($child_node = $this->createNode($top_level_node, $resource_id_map))) {
                    $root_children[] = $child_node;
                }
            }
        }

        foreach ($resource_id_map as $left_over) {
            $root_children[] = new EntityNode($left_over, array());
        }

        $this->root_node = new RootNode($root_children);
        $this->identifier = $tree_data['identifier'];
        $this->revision = isset($tree_data['revision']) ? $tree_data['revision'] : null;

        return $this;
    }

    protected function createNode(array $node_data, array &$resource_id_map)
    {
        $children = array();

        if (!isset($resource_id_map[$node_data['identifier']])) {
            // resource was deleted in the meanwhile.
            return null;
        }

        $resource = $resource_id_map[$node_data['identifier']];

        foreach ($node_data['children'] as $child_node) {
            if (($child_node = $this->createNode($child_node, $resource_id_map))) {
                $children[] = $child_node;
            }
        }

        unset($resource_id_map[$node_data['identifier']]);

        return new EntityNode($resource, $children);
    }
}

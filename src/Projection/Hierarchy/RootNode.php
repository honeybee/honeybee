<?php

namespace Honeybee\Projection\Hierarchy;

class RootNode extends Node
{
    public function getIdentifier()
    {
        return 'root-node';
    }

    public function getLabel()
    {
        return 'Root';
    }
}

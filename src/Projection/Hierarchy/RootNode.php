<?php

namespace Honeybee\Infrastructure\Domain\Tree;

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

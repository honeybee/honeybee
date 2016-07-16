<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publisher;

use Honeybee\Projection\Resource\Resource;

class Publisher extends Resource
{
    public function getName()
    {
        return $this->get('name');
    }

    public function getDescription()
    {
        return $this->get('description');
    }
}

<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publisher;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publisher extends AggregateRoot
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

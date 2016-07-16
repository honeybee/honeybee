<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publication;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publication extends AggregateRoot
{
    public function getYear()
    {
        return $this->get('year');
    }

    public function getDescription()
    {
        return $this->get('description');
    }
}

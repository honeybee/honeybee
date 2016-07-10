<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publisher;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publisher extends AggregateRoot
{
    public function getName()
    {
        return $this->get('name');
    }

    public function setName($name)
    {
        return $this->setValue('name', $name);
    }

    public function getDescription()
    {
        return $this->get('description');
    }

    public function setDescription($description)
    {
        return $this->setValue('description', $description);
    }
}

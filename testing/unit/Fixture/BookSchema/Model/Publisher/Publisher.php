<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publisher;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publisher extends AggregateRoot
{
    public function getName()
    {
        return $this->getValue('name');
    }

    public function setName($name)
    {
        return $this->setValue('name', $name);
    }

    public function getDescription()
    {
        return $this->getValue('description');
    }

    public function setDescription($description)
    {
        return $this->setValue('description', $description);
    }
}

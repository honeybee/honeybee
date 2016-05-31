<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Author;

use Honeybee\Model\Aggregate\AggregateRoot;

class Author extends AggregateRoot
{
    public function getFirstname()
    {
        return $this->getValue('firstname');
    }

    public function setFirstname($firstname)
    {
        return $this->setValue('firstname', $firstname);
    }

    public function getLastname()
    {
        return $this->getValue('lastname');
    }

    public function setLastname($lastname)
    {
        return $this->setValue('lastname', $lastname);
    }
}

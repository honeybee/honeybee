<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author;

use Honeybee\Projection\Projection;

class Author extends Projection
{
    public function getFirstname()
    {
        return $this->get('firstname');
    }

    public function setFirstname($firstname)
    {
        return $this->setValue('firstname', $firstname);
    }

    public function getLastname()
    {
        return $this->get('lastname');
    }

    public function setLastname($lastname)
    {
        return $this->setValue('lastname', $lastname);
    }
}

<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Author;

use Honeybee\Projection\Projection;

class Author extends Projection
{
    public function getFirstname()
    {
        return $this->getValue('firstname');
    }

    public function getLastname()
    {
        return $this->getValue('lastname');
    }

    public function getBirthDate()
    {
        return $this->getValue('birth_date');
    }
}

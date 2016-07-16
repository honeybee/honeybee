<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Book;

use Honeybee\Model\Aggregate\AggregateRoot;

class Book extends AggregateRoot
{
    public function getTitle()
    {
        return $this->get('title');
    }

    public function getDescription()
    {
        return $this->get('description');
    }
}

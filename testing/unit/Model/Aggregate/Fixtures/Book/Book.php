<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Book;

use Honeybee\Model\Aggregate\AggregateRoot;

class Book extends AggregateRoot
{
    public function getTitle()
    {
        return $this->getValue('title');
    }

    public function setTitle($title)
    {
        return $this->setValue('title', $title);
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

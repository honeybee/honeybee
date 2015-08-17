<?php

namespace Honeybee\Tests\Projection\Fixtures\Book;

use Honeybee\Projection\Resource\Resource;

class Book extends Resource
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

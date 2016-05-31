<?php

namespace Honeybee\Tests\Fixture\BookSchema\Projection\Publication;

use Honeybee\Projection\Resource\Resource;

class Publication extends Resource
{
    public function getYear()
    {
        return $this->getValue('year');
    }

    public function setYear($year)
    {
        return $this->setValue('year', $year);
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

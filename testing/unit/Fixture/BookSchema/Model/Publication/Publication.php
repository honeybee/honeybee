<?php

namespace Honeybee\Tests\Fixture\BookSchema\Model\Publication;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publication extends AggregateRoot
{
    public function getYear()
    {
        return $this->get('year');
    }

    public function setYear($year)
    {
        return $this->setValue('year', $year);
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

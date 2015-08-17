<?php

namespace Honeybee\Tests\Model\Aggregate\Fixtures\Publication;

use Honeybee\Model\Aggregate\AggregateRoot;

class Publication extends AggregateRoot
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

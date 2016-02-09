<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class Equals extends Comparison
{
    public function __construct($comparand)
    {
        parent::__construct(Comparison::EQUALS, $comparand);
    }
}

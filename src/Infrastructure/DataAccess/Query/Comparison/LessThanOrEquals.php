<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class LessThanOrEquals extends Comparison
{
    public function __construct($comparand)
    {
        parent::__construct(Comparison::LESS_THAN_EQUAL, $comparand);
    }
}

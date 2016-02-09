<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class GreaterThan extends Comparison
{
    public function __construct($comparand)
    {
        parent::__construct(Comparison::GREATER_THAN, $comparand);
    }
}

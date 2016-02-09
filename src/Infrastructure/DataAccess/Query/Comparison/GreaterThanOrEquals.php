<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class GreaterThanOrEquals extends Comparison
{
    public function __construct($comparand)
    {
        parent::__construct(Comparison::GREATER_THAN_EQUAL, $comparand);
    }
}
<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class LessThan extends Comparison
{
    public function __construct($comparand)
    {
        parent::__construct(Comparison::LESS_THAN, $comparand);
    }
}
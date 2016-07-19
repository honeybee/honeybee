<?php

namespace Honeybee\Infrastructure\DataAccess\Query\Comparison;

use Honeybee\Infrastructure\DataAccess\Query\Comparison;

class In extends Comparison
{
    public function __construct($comparand, $inverted = false)
    {
        parent::__construct(Comparison::IN, $comparand, $inverted);
    }
}

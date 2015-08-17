<?php

namespace Honeybee\Infrastructure\Expression;

interface ExpressionServiceInterface
{
    public function evaluate($expression, array $expression_vars = array());
}

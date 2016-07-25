<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface QueryInterface
{
    public function getOffset();

    public function getLimit();
}

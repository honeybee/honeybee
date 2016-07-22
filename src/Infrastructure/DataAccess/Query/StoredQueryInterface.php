<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface StoredQueryInterface extends QueryInterface
{
    public function getName();

    public function getParameters();
}

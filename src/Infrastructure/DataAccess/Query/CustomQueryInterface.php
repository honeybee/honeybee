<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface CustomQueryInterface extends QueryInterface
{
    public function getParameters();

    public function getQuery();
}

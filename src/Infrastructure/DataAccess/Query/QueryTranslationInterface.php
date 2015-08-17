<?php

namespace Honeybee\Infrastructure\DataAccess\Query;

interface QueryTranslationInterface
{
    public function translate(QueryInterface $query);
}

<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Assert\Assertion;
use Honeybee\Infrastructure\DataAccess\Query\CustomQueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;

class CustomQueryTranslation implements QueryTranslationInterface
{
    public function translate(QueryInterface $query)
    {
        Assertion::isInstanceOf($query, CustomQueryInterface::CLASS);

        return $query->getQuery();
    }
}

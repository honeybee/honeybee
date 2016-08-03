<?php

namespace Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch;

use Assert\Assertion;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Infrastructure\DataAccess\Query\QueryTranslationInterface;
use Honeybee\Infrastructure\DataAccess\Query\StoredQueryInterface;

class StoredQueryTranslation implements QueryTranslationInterface
{
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function translate(QueryInterface $query)
    {
        Assertion::isInstanceOf($query, StoredQueryInterface::CLASS);

        $es_query = [
            'body' => [
                // only support file or id method
                ($this->config->get('method') === 'file' ? 'file' : 'id') => $query->getName(),
                'params' => array_merge(
                    $query->getParameters(),
                    [
                        'from' => $query->getOffset(),
                        'size' => $query->getLimit()
                    ]
                )
            ]
        ];

        return $es_query;
    }
}

<?php

namespace Honeybee\Tests\DataAccess\Finder\Elasticsearch;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\StoredQueryTranslation;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery;
use Honeybee\Infrastructure\DataAccess\Query\StoredQuery;
use Honeybee\Tests\TestCase;

class StoredQueryTranslationTest extends TestCase
{
    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testTranslateUnsupportedQuery()
    {
        $query = new CriteriaQuery(
            new CriteriaList,
            new CriteriaList,
            new CriteriaList,
            0,
            100
        );

        (new StoredQueryTranslation(new ArrayConfig([])))->translate($query);
    }

    public function testTranslateDefault()
    {
        $query = new StoredQuery('test', [], 0, 10);
        $translation = (new StoredQueryTranslation(new ArrayConfig([])))->translate($query);

        $this->assertEquals(
            [
                'body' => [
                    'id' => 'test',
                    'params' => [ 'from' => 0, 'size' => 10 ]
                ]

            ],
            $translation
        );
    }

    public function testTranslateDefaultWithIndexAndType()
    {
        $query = new StoredQuery('test', [], 50, 10);
        $translation = (new StoredQueryTranslation(
            new ArrayConfig([ 'index' => 'test_index', 'type' => 'test_type' ])
        ))->translate($query);

        $this->assertEquals(
            [
                'index' => 'test_index',
                'type' => 'test_type',
                'body' => [ 'id' => 'test', 'params' => [ 'from' => 50, 'size' => 10 ] ]
            ],
            $translation
        );
    }

    public function testTranslateIdMethod()
    {
        $query = new StoredQuery('test', [ 'key' => 'value' ], 10, 10);
        $translation = (new StoredQueryTranslation(new ArrayConfig([ 'method' => 'id' ])))->translate($query);

        $this->assertEquals(
            [
                'body' => [
                    'id' => 'test',
                    'params' => [ 'key' => 'value', 'from' => 10, 'size' => 10 ]
                ]
            ],
            $translation
        );
    }

    public function testTranslateUnknownMethod()
    {
        $query = new StoredQuery('test', [ 'key' => 'value' ], 20, 1);
        $translation = (new StoredQueryTranslation(new ArrayConfig([ 'method' => 'what' ])))->translate($query);

        $this->assertEquals(
            [
                'body' => [
                    'id' => 'test',
                    'params' => [
                        'key' => 'value',
                        'from' => 20,
                        'size' => 1
                    ]
                ]
            ],
            $translation
        );
    }

    public function testTranslateFileMethod()
    {
        $query = new StoredQuery('test', [ 'key' => 'value', 'nested_param' => [ 'key'  => 'value' ] ], 0, 1);
        $translation = (new StoredQueryTranslation(new ArrayConfig([ 'method' => 'file' ])))->translate($query);

        $this->assertEquals(
            [
                'body' => [
                    'file' => 'test',
                    'params' => [
                        'key' => 'value',
                        'nested_param' => [ 'key'  => 'value' ],
                        'from' => 0,
                        'size' => 1
                    ]
                ]
            ],
            $translation
        );
    }
}

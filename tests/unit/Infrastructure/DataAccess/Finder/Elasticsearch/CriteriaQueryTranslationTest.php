<?php

namespace Honeybee\Tests\DataAccess\Finder\Elasticsearch;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\CriteriaQueryTranslation;
use Honeybee\Infrastructure\DataAccess\Query\StoredQuery;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Tests\TestCase;

class CriteriaQueryTranslationTest extends TestCase
{
    /**
     * @dataProvider provideQueryFixture
     */
    public function testTranslate(QueryInterface $query, array $expected_es_query)
    {
        $es_query = (
            new CriteriaQueryTranslation($this->getQueryTranslationConfig())
        )->translate($query);

        $this->assertEquals($expected_es_query, $es_query);
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testTranslateUnsupportedQuery()
    {
        $query = new StoredQuery('invalid', [], 0, 1);
        (new CriteriaQueryTranslation($this->getQueryTranslationConfig()))->translate($query);
    } // @codeCoverageIgnore

    /**
     * @codeCoverageIgnore
     */
    public function provideQueryFixture()
    {
        return include __DIR__ . '/Fixture/criteria_query_translations.php';
    }

    protected function getQueryTranslationConfig()
    {
        return new ArrayConfig([
            'multi_fields' => [ 'username' ]
        ]);
    }
}

<?php

namespace Honeybee\Tests\DataAccess\Finder\Elasticsearch;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\DataAccess\Finder\Elasticsearch\ElasticsearchQueryTranslation;
use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Tests\TestCase;

class ElasticsearchQueryTranslationTest extends TestCase
{
    /**
     * @dataProvider provideQueryFixtures
     */
    public function testTranslate(QueryInterface $query, array $expected_es_query)
    {
        $es_query = (
            new ElasticsearchQueryTranslation($this->getQueryTranslationConfig())
        )->translate($query);

        $this->assertEquals($expected_es_query, $es_query);
    }

    public function provideQueryFixtures()
    {
        return include __DIR__ . '/Fixtures/query_translations.php';
    }

    protected function getQueryTranslationConfig()
    {
        return new ArrayConfig(
            [
                'index' => 'honeybee-system_account',
                'type' => 'user',
                'multi_fields' => [ 'username' ]
            ]
        );
    }
}

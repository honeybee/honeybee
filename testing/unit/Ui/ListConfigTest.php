<?php

namespace Honeybee\Tests\Ui;

use Honeybee\Infrastructure\DataAccess\Query\QueryInterface;
use Honeybee\Tests\TestCase;
use Honeybee\Ui\ListConfig;

class ListConfigTest extends TestCase
{
    public function testGettersEmpty()
    {
        $list_config = new ListConfig;

        $this->assertFalse($list_config->hasSearch());
        $this->assertEquals('', $list_config->hasSearch());
        $this->assertFalse($list_config->hasFilter());
        $this->assertEquals([], $list_config->getFilter());
        $this->assertFalse($list_config->hasSort());
        $this->assertEquals('', $list_config->hasSort());
        $this->assertTrue($list_config->hasLimit());
        $this->assertEquals(50, $list_config->getLimit());
        $this->assertFalse($list_config->hasOffset());
    }

    public function testGetters()
    {
        $list_config = new ListConfig(
            [
                'search' => 'test_search',
                'filter' => [ 'test_filter' ],
                'sort' => 'test_modified',
                'limit' => 25,
                'offset' => 10
            ]
        );

        $this->assertTrue($list_config->hasSearch());
        $this->assertEquals('test_search', $list_config->getSearch());
        $this->assertTrue($list_config->hasFilter());
        $this->assertEquals([ 'test_filter' ], $list_config->getFilter());
        $this->assertTrue($list_config->hasSort());
        $this->assertEquals('test_modified', $list_config->getSort());
        $this->assertTrue($list_config->hasLimit());
        $this->assertEquals(25, $list_config->getLimit());
        $this->assertTrue($list_config->hasOffset());
        $this->assertEquals(10, $list_config->getOffset());
    }

    /**
     * @dataProvider provideListConfig
     */
    public function testAsQuery(ListConfig $list_config, QueryInterface $expected_query)
    {
        $this->assertEquals($expected_query, $list_config->asQuery());
    }

    /**
     * @codeCoverageIgnore
     */
    public function provideListConfig()
    {
        return include __DIR__ . '/Fixture/list_configs.php';
    }
}

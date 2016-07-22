<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\StoredQuery;
use Honeybee\Tests\TestCase;

class StoredQueryTest extends TestCase
{
    public function testConstruct()
    {
        $query = new StoredQuery('template', [ 'param' => 'value' ], 10, 10);

        $this->assertEquals('template', $query->getName());
        $this->assertEquals([ 'param' => 'value' ], $query->getParameters());
    }

    /**
     * @expectedException Assert\InvalidArgumentException
     */
    public function testConstructInvalid()
    {
        $query = new StoredQuery([], [ 'param' => 'value' ], 0, 0);
    }

    public function testToString()
    {
        $query = new StoredQuery('template', [ 'param' => 'value', 'nested' => [ 'param' => 'test' ] ], 0, 1);

        $this->assertEquals(
            'STORED QUERY: SEARCH template PARAMS nested.param=test, param=value LIMIT 1 OFFSET 0',
            (string)$query
        );
    }
}

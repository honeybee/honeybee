<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Tests\TestCase;

class QueryTest extends TestCase
{
    public function testConstruct()
    {
        $query = new Query(
            new CriteriaList,
            new CriteriaList(
                [ new AttributeCriteria('username', new Equals('honeybee-tester')) ]
            ),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        );

        $this->assertEquals(0, $query->getOffset());
        $this->assertEquals(100, $query->getLimit());
        $this->assertInstanceOf(CriteriaList::CLASS, $query->getSearchCriteriaList());
        $this->assertInstanceOf(CriteriaList::CLASS, $query->getFilterCriteriaList());
        $this->assertInstanceOf(CriteriaList::CLASS, $query->getSortCriteriaList());
    }
}

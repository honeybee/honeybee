<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Infrastructure\DataAccess\Query\Comparison\Equals;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaQuery;
use Honeybee\Infrastructure\DataAccess\Query\SortCriteria;
use Honeybee\Tests\TestCase;

class CriteriaQueryTest extends TestCase
{
    public function testConstruct()
    {
        $query = new CriteriaQuery(
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
        $this->assertEquals(new CriteriaList, $query->getSearchCriteriaList());
        $this->assertInstanceOf(CriteriaList::CLASS, $query->getFilterCriteriaList());
        $this->assertEquals(
            new CriteriaList([ new AttributeCriteria('username', new Equals('honeybee-tester')) ]),
            $query->getFilterCriteriaList()
        );
        $this->assertInstanceOf(CriteriaList::CLASS, $query->getSortCriteriaList());
        $this->assertEquals(
            new CriteriaList([ new SortCriteria('created_at') ]),
            $query->getSortCriteriaList()
        );
    }

    public function testCreateCopyWith()
    {
        $query = new CriteriaQuery(
            new CriteriaList(
                [ new AttributeCriteria('field', new Equals('notthis', true)) ]
            ),
            new CriteriaList(
                [ new AttributeCriteria('username', new Equals('honeybee-tester')) ]
            ),
            new CriteriaList([ new SortCriteria('created_at') ]),
            0,
            100
        );

        $copied_query = $query->createCopyWith([
            'search_criteria_list' => new CriteriaList,
            'filter_criteria_list' => new CriteriaList,
            'sort_criteria_list' => new CriteriaList,
            'offset' => 50,
            'limit' => 20
        ]);

        $this->assertEquals(50, $copied_query->getOffset());
        $this->assertEquals(20, $copied_query->getLimit());
        $this->assertInstanceOf(CriteriaList::CLASS, $copied_query->getSearchCriteriaList());
        $this->assertEquals(new CriteriaList, $copied_query->getSearchCriteriaList());
        $this->assertInstanceOf(CriteriaList::CLASS, $copied_query->getFilterCriteriaList());
        $this->assertEquals(new CriteriaList, $copied_query->getFilterCriteriaList());
        $this->assertInstanceOf(CriteriaList::CLASS, $copied_query->getSortCriteriaList());
        $this->assertEquals(new CriteriaList, $copied_query->getSortCriteriaList());
    }
}

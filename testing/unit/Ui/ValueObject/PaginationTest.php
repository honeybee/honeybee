<?php

namespace Honeybee\Tests\Ui\ValueObject;

use Honeybee\Tests\TestCase;
use Honeybee\Ui\ValueObjects\Pagination;

class PaginationTest extends TestCase
{
    public function setUp()
    {
    }

    public function testCreateByOffsetForFirstPage()
    {
        $results = 13;
        $limit = 5;
        $offset = 0;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertTrue($pagination->isFirstPage());
        $this->assertFalse($pagination->isLastPage());
        $this->assertTrue($pagination->hasNextPage());
        $this->assertFalse($pagination->hasPrevPage());
        $this->assertEquals(1, $pagination->getCurrentPageNumber());
        $this->assertEquals(5, $pagination->getNextPageOffset());
        $this->assertEquals(0, $pagination->getPrevPageOffset());
    }

    public function testCreateByOffsetForSomePage()
    {
        $results = 13;
        $limit = 5;
        $offset = 5;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertFalse($pagination->isFirstPage(), 'current page should not be first page');
        $this->assertFalse($pagination->isLastPage(), 'current page should not be last page');
        $this->assertTrue($pagination->hasNextPage(), 'current page should have a next page');
        $this->assertTrue($pagination->hasPrevPage(), 'current page should have a prev page');
        $this->assertEquals(2, $pagination->getCurrentPageNumber(), 'current page number should be correct');
        $this->assertEquals(10, $pagination->getNextPageOffset(), 'offset of next page should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'offset of previous page should be correct');
    }

    public function testCreateByOffsetForLastPage()
    {
        $results = 13;
        $limit = 5;
        $offset = 10;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertFalse($pagination->isFirstPage(), 'current page should not be first page');
        $this->assertTrue($pagination->isLastPage(), 'current page should be last page');
        $this->assertFalse($pagination->hasNextPage(), 'current (last) page should have no next page');
        $this->assertTrue($pagination->hasPrevPage(), 'current (last) page should have a previous page');
        $this->assertEquals(3, $pagination->getCurrentPageNumber(), 'current page number should be correct');
        $this->assertEquals(10, $pagination->getNextPageOffset(), 'next page offset should be correct');
        $this->assertEquals(5, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
    }

    public function testCreateByOffsetWithTooHighOffset()
    {
        $results = 13;
        $limit = 5;
        $offset = 13;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(3, $pagination->getCurrentPageNumber(), 'too high offset should lead to last page number');
        $this->assertEquals(10, $pagination->getCurrentPageOffset(), 'too high offset is should be last page offset');
        $this->assertEquals(5, $pagination->getPrevPageOffset(), 'previous page offset should be correct');

        $this->assertFalse($pagination->isFirstPage(), 'current page should not be first page');
        $this->assertTrue($pagination->isLastPage(), 'current page should be last page');
        $this->assertFalse($pagination->hasNextPage(), 'current (last) page should have no next page');
        $this->assertTrue($pagination->hasPrevPage(), 'current (last) page should have a previous page');
    }

    public function testCreateByOffsetWithNegativeNumberOfResults()
    {
        $results = -1;
        $limit = 5;
        $offset = 13;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(1, $pagination->getCurrentPageNumber(), 'current page number should be reset to 1');
        $this->assertEquals(0, $pagination->getCurrentPageOffset(), 'current page offset should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
        $this->assertEquals(0, $pagination->getNextPageOffset(), 'next page offset should be correct');

        $this->assertTrue($pagination->isFirstPage(), 'current page should be first page');
        $this->assertTrue($pagination->isLastPage(), 'current page should be last page');
        $this->assertFalse($pagination->hasNextPage(), 'current page should have no next page');
        $this->assertFalse($pagination->hasPrevPage(), 'current page should have no previous page');
    }

    public function testCreateByOffsetWithNegativeLimitSilentlyUsesLimit1()
    {
        $results = 13;
        $limit = -5;
        $offset = 0;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(1, $pagination->getCurrentPageNumber(), 'current page number should be 1');
        $this->assertEquals(0, $pagination->getCurrentPageOffset(), 'current page offset should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
        $this->assertEquals(1, $pagination->getNextPageOffset(), 'next page offset should be correct');

        $this->assertTrue($pagination->isFirstPage(), 'current page should be first page');
        $this->assertFalse($pagination->isLastPage(), 'current page should be last page');
        $this->assertTrue($pagination->hasNextPage(), 'current page should have no next page');
        $this->assertFalse($pagination->hasPrevPage(), 'current page should have no previous page');
    }

    public function testCreateByOffsetWithNegativeOffsetSilentlyUsesOffset0()
    {
        $results = 13;
        $limit = 5;
        $offset = -13;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(1, $pagination->getCurrentPageNumber(), 'current page number should be 1');
        $this->assertEquals(0, $pagination->getCurrentPageOffset(), 'current page offset should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
        $this->assertEquals(5, $pagination->getNextPageOffset(), 'next page offset should be correct');

        $this->assertTrue($pagination->isFirstPage(), 'current page should be first page');
        $this->assertFalse($pagination->isLastPage(), 'current page should be last page');
        $this->assertTrue($pagination->hasNextPage(), 'current page should have no next page');
        $this->assertFalse($pagination->hasPrevPage(), 'current page should have no previous page');
    }

    public function testCreateByOffsetWithFloatValuesAsString()
    {
        $results = "13.0";
        $limit = "5.9";
        $offset = "0.0";

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(1, $pagination->getCurrentPageNumber(), 'current page number should be 1');
        $this->assertEquals(0, $pagination->getCurrentPageOffset(), 'current page offset should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
        $this->assertEquals(5, $pagination->getNextPageOffset(), 'next page offset should be correct');

        $this->assertTrue($pagination->isFirstPage(), 'current page should be first page');
        $this->assertFalse($pagination->isLastPage(), 'current page should be last page');
        $this->assertTrue($pagination->hasNextPage(), 'current page should have no next page');
        $this->assertFalse($pagination->hasPrevPage(), 'current page should have no previous page');
    }

    public function testCreateByOffsetWithNullValueArguments()
    {
        $results = null;
        $limit = null;
        $offset = null;

        $pagination = Pagination::createByOffset($results, $limit, $offset);

        $this->assertInstanceOf('Honeybee\\Ui\\ValueObjects\\Pagination', $pagination);

        $this->assertEquals(1, $pagination->getCurrentPageNumber(), 'current page number should be 1');
        $this->assertEquals(0, $pagination->getCurrentPageOffset(), 'current page offset should be correct');
        $this->assertEquals(0, $pagination->getPrevPageOffset(), 'previous page offset should be correct');
        $this->assertEquals(0, $pagination->getNextPageOffset(), 'next page offset should be correct');

        $this->assertTrue($pagination->isFirstPage(), 'current page should be first page');
        $this->assertTrue($pagination->isLastPage(), 'current page should be last page');
        $this->assertFalse($pagination->hasNextPage(), 'current page should have no next page');
        $this->assertFalse($pagination->hasPrevPage(), 'current page should have no previous page');
    }
}

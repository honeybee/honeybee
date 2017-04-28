<?php

namespace Honeybee\Tests\DataAccess\Finder;

use Honeybee\Infrastructure\DataAccess\Finder\FinderResultInterface;
use Honeybee\Infrastructure\DataAccess\Finder\FinderResult;
use Honeybee\Tests\TestCase;
use Mockery;

class FinderResultTest extends TestCase
{
    public function testConstruct()
    {
        $r = new FinderResult;

        $this->assertInstanceOf(FinderResultInterface::CLASS, $r);
        $this->assertEquals(0, $r->getTotalCount());
        $this->assertEquals(0, $r->getCount());
        $this->assertEquals(0, $r->getOffset());
        $this->assertFalse($r->hasResults());
        $this->assertEquals([], $r->getResults());
        $this->assertNull($r->getFirstResult());
    }

    public function testEmptyStaticConstructorWorks()
    {
        $r = FinderResult::makeEmpty();

        $this->assertInstanceOf(FinderResultInterface::CLASS, $r);
        $this->assertEquals(0, $r->getTotalCount());
        $this->assertEquals(0, $r->getCount());
        $this->assertEquals(0, $r->getOffset());
        $this->assertFalse($r->hasResults());
        $this->assertEquals([], $r->getResults());
        $this->assertNull($r->getFirstResult());
    }
}

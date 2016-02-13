<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Tests\TestCase;
use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;

class AttributeCriteriaTest extends TestCase
{
    public function testConstruct()
    {
        $criteria = new AttributeCriteria('username', 'honeybee-tester');

        $this->assertEquals('username', $criteria->getAttributePath());
        $this->assertEquals('honeybee-tester', $criteria->getValue());
        $this->assertEquals(AttributeCriteria::EQUALS, $criteria->getComparator());
        $this->assertEquals('ATTRIBUTE username EQ honeybee-tester', (string)$criteria);
    }
}

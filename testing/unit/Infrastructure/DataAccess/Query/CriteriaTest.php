<?php

namespace Honeybee\Tests\DataAccess\Query;

use Honeybee\Infrastructure\DataAccess\Query\AttributeCriteria;
use Honeybee\Tests\TestCase;

class AttributeCriteriaTest extends TestCase
{
    public function testConstruct()
    {
        $criteria = new AttributeCriteria('username', 'honeybee-tester');

        $this->assertEquals('username', $criteria->getAttributePath());
        $this->assertEquals('honeybee-tester', $criteria->getValue());
        $this->assertEquals(AttributeCriteria::EQUALS, $criteria->getComparator());
    }
}
